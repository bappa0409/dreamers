<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionPaymentController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'status'=>'nullable|in:pending,verified,rejected',
            'payment_method'=>'nullable|in:cash,bank,mobile_banking,online',
            'search'=>'nullable|string|max:150',
            'from_date'=>'nullable|date_format:Y-m-d',
            'to_date'=>'nullable|date_format:Y-m-d|after_or_equal:from_date',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        |
        | Search/method/date filters apply.
        | Status filter is intentionally ignored so cards show all statuses.
        |
        */

        $summaryQuery=$this->query(
            $validated,
            false,
            false
        );

        $summary=$summaryQuery
            ->selectRaw("
                COUNT(*) total,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
                SUM(CASE WHEN status='verified' THEN 1 ELSE 0 END) verified,
                SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) rejected,
                COALESCE(SUM(CASE WHEN status='verified' THEN amount ELSE 0 END),0) verified_amount
            ")
            ->first();

        $perPage=min(
            max(
                (int)($validated['per_page']??15),
                5
            ),
            100
        );

        $payments=$this->query(
            $validated,
            true,
            true
        )
            ->latest('paid_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success'=>true,
            'data'=>$payments,
            'summary'=>[
                'total'=>(int)($summary->total??0),
                'pending'=>(int)($summary->pending??0),
                'verified'=>(int)($summary->verified??0),
                'rejected'=>(int)($summary->rejected??0),
                'verified_amount'=>round(
                    (float)($summary->verified_amount??0),
                    2
                ),
            ],
        ]);
    }

    /**
     * Outstanding (unpaid/partial/overdue) dues for a given member,
     * used to populate the "Add Payment" form in the admin panel.
     */
    public function outstandingDues(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>'required|integer|exists:members,id',
        ]);

        $dues=SubscriptionDue::query()
            ->select([
                'id',
                'member_subscription_id',
                'year',
                'month',
                'base_amount',
                'share_count',
                'fine_amount',
                'amount',
                'paid_amount',
                'due_date',
                'status',
            ])
            ->whereHas(
                'subscription',
                function($query)use($validated){
                    $query->where(
                        'member_id',
                        $validated['member_id']
                    );
                }
            )
            ->whereIn('status',[
                'unpaid',
                'partial',
                'overdue',
            ])
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function($due){
                $due->outstanding=round(
                    (float)$due->amount-(float)$due->paid_amount,
                    2
                );

                return $due;
            });

        return response()->json([
            'success'=>true,
            'data'=>$dues,
        ]);
    }

    /**
     * Admin-recorded subscription payment (e.g. cash collected in person).
     * Created and verified in a single step since the admin is the one
     * confirming the payment was received.
     */
    public function store(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>'required|integer|exists:members,id',
            'subscription_due_id'=>[
                'required',
                'integer',
                Rule::exists('subscription_dues','id'),
            ],
            'amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'payment_method'=>'required|in:cash,bank,mobile_banking,online',
            'transaction_reference'=>'nullable|string|max:255',
            'note'=>'nullable|string|max:2000',
        ]);

        $member=Member::query()
            ->findOrFail($validated['member_id']);

        $due=SubscriptionDue::query()
            ->findOrFail($validated['subscription_due_id']);

        abort_unless(
            $due->subscription()
                ->where('member_id',$member->id)
                ->exists(),
            422,
            'This subscription due does not belong to the selected member.'
        );

        $payment=$this->subscriptionService->submitPayment(
            $member,
            $due,
            [
                'amount'=>$validated['amount'],
                'payment_method'=>$validated['payment_method'],
                'transaction_reference'=>isset(
                    $validated['transaction_reference']
                )
                    ?trim($validated['transaction_reference'])
                    :null,
            ]
        );

        $payment=$this->subscriptionService->verifyPayment(
            $payment,
            $request->user()->id,
            isset($validated['note'])
                ?trim($validated['note'])
                :'Recorded and verified by admin.'
        );

        return response()->json([
            'success'=>true,
            'message'=>'Subscription payment recorded and verified successfully.',
            'data'=>$payment,
        ],201);
    }

    public function show(
        SubscriptionPayment $subscriptionPayment
    ){
        $subscriptionPayment->load([
            'member:id,user_id,member_code,status',
            'member.user:id,name,email,mobile',
            'due.subscription.plan',
            'verifier:id,name,email',
            'financeTransaction.entries.account',
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$subscriptionPayment,
        ]);
    }

    public function verify(
        Request $request,
        SubscriptionPayment $subscriptionPayment
    ){
        $validated=$request->validate([
            'note'=>'nullable|string|max:2000',
        ]);

        $payment=$this->subscriptionService
            ->verifyPayment(
                $subscriptionPayment,
                $request->user()->id,
                isset($validated['note'])
                    ?trim($validated['note'])
                    :null
            );

        return response()->json([
            'success'=>true,
            'message'=>'Subscription payment verified successfully.',
            'data'=>$payment,
        ]);
    }

    public function reject(
        Request $request,
        SubscriptionPayment $subscriptionPayment
    ){
        $validated=$request->validate([
            'reason'=>'required|string|max:2000',
        ]);

        $payment=$this->subscriptionService
            ->rejectPayment(
                $subscriptionPayment,
                $request->user()->id,
                trim($validated['reason'])
            );

        return response()->json([
            'success'=>true,
            'message'=>'Subscription payment rejected successfully.',
            'data'=>$payment,
        ]);
    }

    private function query(
        array $filters,
        bool $applyStatus=true,
        bool $relations=true
    ): Builder{
        $query=SubscriptionPayment::query();

        if($relations){
            $query->with([
                'member:id,user_id,member_code',
                'member.user:id,name,email,mobile',
                'due:id,member_subscription_id,year,month,base_amount,fine_amount,amount,paid_amount,due_date,status',
                'verifier:id,name,email',
            ]);
        }

        if(
            $applyStatus&&
            !empty($filters['status'])
        ){
            $query->where(
                'status',
                $filters['status']
            );
        }

        if(!empty($filters['payment_method'])){
            $query->where(
                'payment_method',
                $filters['payment_method']
            );
        }

        if(!empty($filters['from_date'])){
            $query->whereDate(
                'paid_at',
                '>=',
                $filters['from_date']
            );
        }

        if(!empty($filters['to_date'])){
            $query->whereDate(
                'paid_at',
                '<=',
                $filters['to_date']
            );
        }

        if(!empty($filters['search'])){
            $search=trim(
                $filters['search']
            );

            $query->where(function($q)use($search){
                $q->where(
                    'payment_no',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'transaction_reference',
                    'like',
                    "%{$search}%"
                )
                ->orWhereHas(
                    'member',
                    function($member)use($search){
                        $member
                            ->where(
                                'member_code',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'user',
                                function($user)use($search){
                                    $user
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'mobile',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                    }
                );
            });
        }

        return $query;
    }
}