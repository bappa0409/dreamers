<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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