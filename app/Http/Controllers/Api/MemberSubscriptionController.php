<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPayment;
use App\Services\MemberDashboardService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class MemberSubscriptionController extends Controller
{
    public function __construct(
        protected MemberDashboardService $memberDashboardService,
        protected SubscriptionService $subscriptionService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'year'=>'nullable|integer|min:2000|max:2100',
        ]);

        $member=$this->activeMember($request);
        $year=(int)($validated['year']??now()->year);

        $subscription=$member->subscriptions()
            ->select([
                'id',
                'member_id',
                'subscription_plan_id',
                'start_date',
                'end_date',
                'is_active',
            ])
            ->where('is_active',true)
            ->with([
                'plan:id,name,amount,due_day,fine_type,fine_value,grace_days,is_active',
            ])
            ->latest('id')
            ->first();
            
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
                'fine_applied_at',
                'status',
            ])
            ->whereHas(
                'subscription',
                fn($q)=>$q->where('member_id',$member->id)
            )
            ->where('year',$year)
            ->orderByDesc('month')
            ->get()
            ->map(fn(SubscriptionDue $due)=>[
                'id'=>$due->id,
                'year'=>(int)$due->year,
                'month'=>(int)$due->month,
                'period'=>$due->period,
                'base_amount'=>round((float)$due->base_amount,2),
                'share_count'=>max((int)$due->share_count,1),
                'fine_amount'=>round((float)$due->fine_amount,2),
                'amount'=>round((float)$due->amount,2),
                'paid_amount'=>round((float)$due->paid_amount,2),
                'outstanding'=>round($due->outstanding,2),
                'due_date'=>$due->due_date?->toDateString(),
                'fine_applied_at'=>$due->fine_applied_at?->toDateString(),
                'status'=>$due->status,
            ])
            ->values();

        return response()->json([
            'success'=>true,
            'data'=>[
                'subscription'=>$subscription,
                'summary'=>$this->memberDashboardService->summary($member),
                'dues'=>$dues,
            ],
        ]);
    }

    public function payments(Request $request)
    {
        $validated=$request->validate([
            'year'=>'nullable|integer|min:2000|max:2100',
            'status'=>'nullable|in:pending,verified,rejected',
            'payment_method'=>'nullable|in:cash,bank,mobile_banking,online',
            'search'=>'nullable|string|max:150',
            'page'=>'nullable|integer|min:1',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $member=$this->activeMember($request);
        $year=(int)($validated['year']??now()->year);
        $perPage=min(
            max((int)($validated['per_page']??20),5),
            100
        );

        $base=SubscriptionPayment::query()
            ->where('member_id',$member->id)
            ->whereHas(
                'due',
                fn($q)=>$q->where('year',$year)
            )
            ->when(
                !empty($validated['search']),
                function($q)use($validated){
                    $search=trim($validated['search']);

                    $q->where(function($q)use($search){
                        $q->where('payment_no','like',"%{$search}%")
                            ->orWhere(
                                'transaction_reference',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                !empty($validated['payment_method']),
                fn($q)=>$q->where(
                    'payment_method',
                    $validated['payment_method']
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Summary ignores status filter
        |--------------------------------------------------------------------------
        |
        | Search/year/payment-method filters apply to cards.
        | This lets Pending/Verified/Rejected cards remain useful while filtering.
        |
        */
        $summary=(clone $base)
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status='verified' THEN 1 ELSE 0 END) AS verified,
                SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) AS rejected,
                COALESCE(
                    SUM(
                        CASE
                            WHEN status='verified'
                            THEN amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS verified_amount
            ")
            ->first();

        $payments=(clone $base)
            ->select([
                'id',
                'payment_no',
                'member_id',
                'subscription_due_id',
                'amount',
                'payment_method',
                'transaction_reference',
                'status',
                'paid_at',
                'verified_at',
                'verification_note',
            ])
            ->when(
                !empty($validated['status']),
                fn($q)=>$q->where(
                    'status',
                    $validated['status']
                )
            )
            ->with([
                'due:id,year,month',
            ])
            ->latest('paid_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success'=>true,
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
            'data'=>$payments,
        ]);
    }

    public function pay(
        Request $request,
        SubscriptionDue $subscriptionDue
    ){
        $validated=$request->validate([
            'amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'payment_method'=>'required|in:cash,bank,mobile_banking,online',
            'transaction_reference'=>'nullable|string|max:255',
        ]);

        $member=$this->activeMember($request);

        /*
        |--------------------------------------------------------------------------
        | Ownership check
        |--------------------------------------------------------------------------
        |
        | Prevents a member from submitting payment against another member's due.
        |
        */
        abort_unless(
            $subscriptionDue->subscription()
                ->where('member_id',$member->id)
                ->exists(),
            404,
            'Subscription due not found.'
        );

        $payment=$this->subscriptionService->submitPayment(
            $member,
            $subscriptionDue,
            [
                ...$validated,
                'transaction_reference'=>isset(
                    $validated['transaction_reference']
                )
                    ?trim($validated['transaction_reference'])
                    :null,
            ]
        );

        return response()->json([
            'success'=>true,
            'message'=>'Payment submitted successfully and is awaiting verification.',
            'data'=>$payment,
        ],201);
    }

    protected function activeMember(Request $request)
    {
        $member=$request->user()
            ->member()
            ->select([
                'id',
                'user_id',
                'member_code',
                'status',
            ])
            ->first();

        abort_unless(
            $member&&$member->status==='active',
            403,
            'Active membership is required.'
        );

        return $member;
    }
}