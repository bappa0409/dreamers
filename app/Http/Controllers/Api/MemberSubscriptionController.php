<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPayment;
use App\Services\MemberDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberSubscriptionController extends Controller
{
    public function __construct(
        protected MemberDashboardService $memberDashboardService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'year'=>'nullable|integer|min:2000|max:2100',
        ]);

        $member=$request->user()->member;

        abort_unless(
            $member&&$member->status==='active',
            403,
            'Active membership is required.'
        );

        $year=(int)($validated['year']??now()->year);

        $subscription=$member->subscriptions()
            ->where('is_active',true)
            ->with('plan')
            ->latest('id')
            ->first();

        $dues=SubscriptionDue::query()
            ->whereHas('subscription',function($query)use($member){
                $query->where('member_id',$member->id);
            })
            ->where('year',$year)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function($due){
                $baseAmount=max(
                    (float)$due->amount-
                    (float)($due->fine_amount??0),
                    0
                );

                $shareCount=max(
                    (int)($due->share_count??1),
                    1
                );

                return[
                    'id'=>$due->id,
                    'year'=>$due->year,
                    'month'=>$due->month,
                    'base_amount'=>round($baseAmount,2),
                    'share_count'=>$shareCount,
                    'fine_amount'=>round(
                        (float)($due->fine_amount??0),
                        2
                    ),
                    'amount'=>round(
                        (float)$due->amount,
                        2
                    ),
                    'paid_amount'=>round(
                        (float)$due->paid_amount,
                        2
                    ),
                    'outstanding'=>round(
                        max(
                            (float)$due->amount-
                            (float)$due->paid_amount,
                            0
                        ),
                        2
                    ),
                    'due_date'=>$due->due_date
                        ?->toDateString(),
                    'fine_applied_at'=>$due->fine_applied_at
                        ?->toDateTimeString(),
                    'status'=>$due->status,
                ];
            })
            ->values();

        $payments=SubscriptionPayment::query()
            ->where('member_id',$member->id)
            ->whereHas('due',function($query)use($year){
                $query->where('year',$year);
            })
            ->with([
                'due:id,year,month',
            ])
            ->latest('id')
            ->get()
            ->map(function($payment){
                return[
                    'id'=>$payment->id,
                    'payment_no'=>$payment->payment_no,
                    'amount'=>round(
                        (float)$payment->amount,
                        2
                    ),
                    'payment_method'=>$payment->payment_method,
                    'transaction_reference'=>
                        $payment->transaction_reference,
                    'paid_at'=>$payment->paid_at
                        ?->toDateTimeString(),
                    'status'=>$payment->status,
                    'due'=>$payment->due
                        ?[
                            'id'=>$payment->due->id,
                            'year'=>$payment->due->year,
                            'month'=>$payment->due->month,
                        ]
                        :null,
                ];
            })
            ->values();

        return response()->json([
            'success'=>true,
            'data'=>[
                'subscription'=>$subscription,
                'summary'=>$this->memberDashboardService
                    ->summary($member),
                'dues'=>$dues,
                'payments'=>$payments,
            ],
        ]);
    }

    public function pay(
        Request $request,
        SubscriptionDue $subscriptionDue
    ){
        $validated=$request->validate([
            'amount'=>'required|numeric|min:0.01',
            'payment_method'=>
                'required|in:cash,bank,mobile_banking,online',
            'transaction_reference'=>
                'nullable|string|max:255',
        ]);

        $member=$request->user()->member;

        abort_unless(
            $member&&$member->status==='active',
            403,
            'Active membership is required.'
        );

        $subscriptionDue->loadMissing('subscription');

        abort_unless(
            $subscriptionDue->subscription&&
            (int)$subscriptionDue->subscription->member_id===
            (int)$member->id,
            403,
            'You cannot pay another member\'s subscription.'
        );

        $payment=DB::transaction(function()use(
            $request,
            $subscriptionDue,
            $member,
            $validated
        ){
            $due=SubscriptionDue::query()
                ->whereKey($subscriptionDue->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array(
                $due->status,
                ['paid','waived'],
                true
            )){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'This subscription has already been settled.'
                    ],
                ]);
            }

            $outstanding=max(
                round(
                    (float)$due->amount-
                    (float)$due->paid_amount,
                    2
                ),
                0
            );

            if($outstanding<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'There is no outstanding amount for this subscription.'
                    ],
                ]);
            }

            $amount=round(
                (float)$validated['amount'],
                2
            );

            if($amount>$outstanding){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Payment amount cannot exceed the outstanding balance.'
                    ],
                ]);
            }

            $pendingAmount=round(
                (float)SubscriptionPayment::query()
                    ->where(
                        'subscription_due_id',
                        $due->id
                    )
                    ->where('status','pending')
                    ->sum('amount'),
                2
            );

            $availableToSubmit=max(
                round(
                    $outstanding-$pendingAmount,
                    2
                ),
                0
            );

            if($availableToSubmit<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'A payment for the full outstanding amount is already awaiting verification.'
                    ],
                ]);
            }

            if($amount>$availableToSubmit){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Considering pending payments, you can submit a maximum of '.
                        number_format($availableToSubmit,2).
                        '.'
                    ],
                ]);
            }

            return SubscriptionPayment::create([
                'subscription_due_id'=>$due->id,
                'member_id'=>$member->id,
                'payment_no'=>$this->generatePaymentNumber(),
                'amount'=>$amount,
                'payment_method'=>$validated['payment_method'],
                'transaction_reference'=>
                    $validated['transaction_reference']??null,
                'paid_at'=>now(),
                'status'=>'pending',
                'submitted_by'=>$request->user()->id,
            ]);
        });

        return response()->json([
            'success'=>true,
            'message'=>
                'Payment submitted successfully and is awaiting verification.',
            'data'=>$payment,
        ],201);
    }

    protected function generatePaymentNumber(): string
    {
        $prefix='SUB-'.now()->format('Ym').'-';

        $last=SubscriptionPayment::query()
            ->where(
                'payment_no',
                'like',
                $prefix.'%'
            )
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('payment_no');

        $next=$last
            ?((int)substr($last,-6))+1
            :1;

        return $prefix.str_pad(
            (string)$next,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}