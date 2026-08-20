<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberSubscription;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function __construct(
        protected AccountingService $accounting
    ){}

    public function generateDue(
        MemberSubscription $subscription,
        int $year,
        int $month,
        ?int $userId=null
    ): SubscriptionDue{
        return DB::transaction(function()use($subscription,$year,$month,$userId){
            $subscription=MemberSubscription::query()
                ->whereKey($subscription->id)
                ->lockForUpdate()
                ->firstOrFail();

            $subscription->loadMissing([
                'plan',
                'member.user'
            ]);

            if(!$subscription->is_active){
                throw ValidationException::withMessages([
                    'subscription'=>['The member subscription is inactive.']
                ]);
            }

            if(!$subscription->plan){
                throw ValidationException::withMessages([
                    'subscription'=>['Subscription plan not found.']
                ]);
            }

            if(!$subscription->member){
                throw ValidationException::withMessages([
                    'subscription'=>['Member not found.']
                ]);
            }

            if($month<1||$month>12){
                throw ValidationException::withMessages([
                    'month'=>['Invalid subscription month.']
                ]);
            }

            $period=Carbon::create($year,$month,1)->startOfMonth();

            if(
                $subscription->start_date&&
                $period->copy()->endOfMonth()->lt(
                    $subscription->start_date->copy()->startOfMonth()
                )
            ){
                throw ValidationException::withMessages([
                    'month'=>['This period is before the subscription start date.']
                ]);
            }

            if(
                $subscription->end_date&&
                $period->copy()->startOfMonth()->gt(
                    $subscription->end_date->copy()->endOfMonth()
                )
            ){
                throw ValidationException::withMessages([
                    'month'=>['This period is after the subscription end date.']
                ]);
            }

            $existing=SubscriptionDue::query()
                ->where('member_subscription_id',$subscription->id)
                ->where('year',$year)
                ->where('month',$month)
                ->lockForUpdate()
                ->first();

            if($existing){
                return $existing;
            }

            $shareEnabled=$this->shareEnabled();

            if($shareEnabled){
                $shareCount=$subscription->member
                    ->activeShares()
                    ->whereNotNull('acquired_date')
                    ->whereDate(
                        'acquired_date',
                        '<=',
                        $period->copy()->endOfMonth()->toDateString()
                    )
                    ->count();

                if($shareCount<1){
                    throw ValidationException::withMessages([
                        'subscription'=>[
                            'The member has no active shares for this subscription period.'
                        ]
                    ]);
                }
            }else{
                $shareCount=1;
            }

            $baseAmount=round(
                (float)$subscription->plan->amount*$shareCount,
                2
            );

            $dueDay=max(
                1,
                min(
                    (int)$subscription->plan->due_day,
                    $period->daysInMonth
                )
            );

            $due=SubscriptionDue::create([
                'member_subscription_id'=>$subscription->id,
                'year'=>$year,
                'month'=>$month,
                'base_amount'=>$baseAmount,
                'share_count'=>$shareCount,
                'fine_amount'=>0,
                'amount'=>$baseAmount,
                'paid_amount'=>0,
                'due_date'=>$period->copy()->day($dueDay)->toDateString(),
                'fine_applied_at'=>null,
                'status'=>'unpaid'
            ]);

            $receivable=$this->accounting->account('receivable');
            $subscriptionIncome=$this->accounting->account('subscription_income');

            $memberName=$subscription->member->user?->name
                ??$subscription->member->member_code
                ??'Member';

            $journal=$this->accounting->post([
                'transaction_date'=>$period->copy()->startOfMonth()->toDateString(),
                'type'=>'subscription_due',
                'source_module'=>'subscription_due',
                'source_id'=>$due->id,
                'reference_type'=>SubscriptionDue::class,
                'reference_id'=>$due->id,
                'description'=>"Monthly subscription due - {$memberName} - {$period->format('F Y')} ({$shareCount} share".($shareCount>1?'s':'').")",
                'user_id'=>$userId??auth()->id(),
                'entries'=>[
                    [
                        'account_id'=>$receivable->id,
                        'debit'=>$baseAmount,
                        'credit'=>0,
                        'description'=>'Subscription receivable'
                    ],
                    [
                        'account_id'=>$subscriptionIncome->id,
                        'debit'=>0,
                        'credit'=>$baseAmount,
                        'description'=>'Monthly subscription income'
                    ]
                ]
            ]);

            $due->update([
                'finance_transaction_id'=>$journal->id
            ]);

            return $due->fresh([
                'subscription.plan',
                'subscription.member.user',
                'financeTransaction.entries.account'
            ]);
        });
    }

    public function assignDefaultSubscription(
        Member $member,
        ?int $userId=null,
        bool $generateCurrentDue=true
    ): MemberSubscription{
        return DB::transaction(function()use($member,$userId,$generateCurrentDue){
            $member=Member::query()
                ->whereKey($member->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member'=>[
                        'Subscription can only be assigned to an active member.'
                    ]
                ]);
            }

            $subscription=MemberSubscription::query()
                ->where('member_id',$member->id)
                ->where('is_active',true)
                ->with('plan')
                ->first();

            if(!$subscription){
                $plan=SubscriptionPlan::query()
                    ->where('is_default',true)
                    ->where('is_active',true)
                    ->first();

                if(!$plan){
                    throw ValidationException::withMessages([
                        'subscription'=>[
                            'Active default subscription plan is not configured.'
                        ]
                    ]);
                }

                $subscription=MemberSubscription::create([
                    'member_id'=>$member->id,
                    'subscription_plan_id'=>$plan->id,
                    'start_date'=>$member->joining_date??now()->toDateString(),
                    'end_date'=>null,
                    'is_active'=>true
                ]);

                $subscription->load('plan');
            }

            if($generateCurrentDue){
                $this->generateDue(
                    $subscription,
                    now()->year,
                    now()->month,
                    $userId
                );
            }

            return $subscription->fresh('plan');
        });
    }

    public function submitPayment(
        Member $member,
        SubscriptionDue $due,
        array $data
    ): SubscriptionPayment{
        return DB::transaction(function()use($member,$due,$data){
            $member=Member::query()
                ->whereKey($member->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member'=>[
                        'Only active members can submit subscription payments.'
                    ]
                ]);
            }

            $due=SubscriptionDue::query()
                ->with('subscription')
                ->whereKey($due->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(
                !$due->subscription||
                (int)$due->subscription->member_id!==(int)$member->id
            ){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'This subscription due does not belong to the member.'
                    ]
                ]);
            }

            if(in_array($due->status,['paid','waived'],true)){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'This subscription due is already settled.'
                    ]
                ]);
            }

            $outstanding=max(
                round(
                    (float)$due->amount-(float)$due->paid_amount,
                    2
                ),
                0
            );

            if($outstanding<=0){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'There is no outstanding amount for this due.'
                    ]
                ]);
            }

            $pendingAmount=round(
                (float)SubscriptionPayment::query()
                    ->where('subscription_due_id',$due->id)
                    ->where('status','pending')
                    ->sum('amount'),
                2
            );

            $available=max(
                round(
                    $outstanding-$pendingAmount,
                    2
                ),
                0
            );

            if($available<=0){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'The full outstanding amount is already awaiting verification.'
                    ]
                ]);
            }

            $amount=round(
                (float)($data['amount']??0),
                2
            );

            if($amount<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Payment amount must be greater than zero.'
                    ]
                ]);
            }

            if($amount>$available){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Maximum payable amount is '.number_format($available,2).'.'
                    ]
                ]);
            }

            $paymentMethod=$data['payment_method']??null;

            if(!in_array(
                $paymentMethod,
                ['cash','bank','mobile_banking','online'],
                true
            )){
                throw ValidationException::withMessages([
                    'payment_method'=>[
                        'Invalid payment method.'
                    ]
                ]);
            }

            return SubscriptionPayment::create([
                'payment_no'=>$this->generatePaymentNumber(),
                'member_id'=>$member->id,
                'subscription_due_id'=>$due->id,
                'amount'=>$amount,
                'payment_method'=>$paymentMethod,
                'transaction_reference'=>$data['transaction_reference']??null,
                'status'=>'pending',
                'paid_at'=>now()
            ]);
        });
    }

    public function verifyPayment(
        SubscriptionPayment $payment,
        int $verifiedBy,
        ?string $note=null
    ): SubscriptionPayment{
        return DB::transaction(function()use($payment,$verifiedBy,$note){
            $payment=SubscriptionPayment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($payment->status==='verified'){
                return $payment->fresh([
                    'due',
                    'member.user',
                    'verifier',
                    'financeTransaction.entries.account'
                ]);
            }

            if($payment->status!=='pending'){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'Only pending payments can be verified.'
                    ]
                ]);
            }

            if($payment->finance_transaction_id){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'Accounting transaction already exists for this payment.'
                    ]
                ]);
            }

            $due=SubscriptionDue::query()
                ->whereKey($payment->subscription_due_id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($due->status,['paid','waived'],true)){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'The subscription due has already been settled.'
                    ]
                ]);
            }

            $outstanding=max(
                round(
                    (float)$due->amount-(float)$due->paid_amount,
                    2
                ),
                0
            );

            $amount=round(
                (float)$payment->amount,
                2
            );

            if($outstanding<=0){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'There is no outstanding balance for this due.'
                    ]
                ]);
            }

            if($amount>$outstanding){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'Payment amount exceeds the current outstanding balance.'
                    ]
                ]);
            }

            $receiveAccount=$this->resolvePaymentAccount(
                $payment->payment_method
            );

            $receivableAccount=$this->accounting->account(
                'receivable'
            );

            $transaction=$this->accounting->post([
                'transaction_date'=>$payment->paid_at?->toDateString()
                    ??now()->toDateString(),
                'type'=>'subscription_payment',
                'source_module'=>'subscription_payment',
                'source_id'=>$payment->id,
                'reference_type'=>SubscriptionPayment::class,
                'reference_id'=>$payment->id,
                'description'=>"Subscription payment {$payment->payment_no}",
                'user_id'=>$verifiedBy,
                'entries'=>[
                    [
                        'account_id'=>$receiveAccount->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>"Subscription payment received - {$payment->payment_no}"
                    ],
                    [
                        'account_id'=>$receivableAccount->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>"Receivable settlement - {$payment->payment_no}"
                    ]
                ]
            ]);

            $newPaid=round(
                (float)$due->paid_amount+$amount,
                2
            );

            $newPaid=min(
                $newPaid,
                (float)$due->amount
            );

            $due->update([
                'paid_amount'=>$newPaid,
                'status'=>$newPaid>=(float)$due->amount
                    ?'paid'
                    :'partial'
            ]);

            $payment->update([
                'status'=>'verified',
                'verified_by'=>$verifiedBy,
                'verified_at'=>now(),
                'verification_note'=>$note,
                'finance_transaction_id'=>$transaction->id
            ]);

            return $payment->fresh([
                'due.subscription.plan',
                'member.user',
                'verifier',
                'financeTransaction.entries.account'
            ]);
        });
    }

    public function rejectPayment(
        SubscriptionPayment $payment,
        int $userId,
        string $reason
    ): SubscriptionPayment{
        return DB::transaction(function()use($payment,$userId,$reason){
            $payment=SubscriptionPayment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($payment->status!=='pending'){
                throw ValidationException::withMessages([
                    'payment'=>[
                        'Only pending payments can be rejected.'
                    ]
                ]);
            }

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'reason'=>[
                        'Rejection reason is required.'
                    ]
                ]);
            }

            $payment->update([
                'status'=>'rejected',
                'verified_by'=>$userId,
                'verified_at'=>now(),
                'verification_note'=>$reason
            ]);

            return $payment->fresh([
                'member.user',
                'due.subscription.plan',
                'verifier'
            ]);
        });
    }

    public function applyLateFine(
        SubscriptionDue $due,
        ?int $userId=null
    ): SubscriptionDue{
        return DB::transaction(function()use($due,$userId){
            $due=SubscriptionDue::query()
                ->whereKey($due->id)
                ->lockForUpdate()
                ->firstOrFail();

            $due->loadMissing([
                'subscription.member.user'
            ]);

            if(in_array($due->status,['paid','waived'],true)){
                return $due;
            }

            if($due->fine_applied_at){
                return $due;
            }

            if(!$this->lateFineEnabled()){
                return $due;
            }

            if(!$this->isFineApplicable($due)){
                return $due;
            }

            $fineAmount=$this->calculateLateFine($due);

            if($fineAmount<=0){
                return $due;
            }

            $receivable=$this->accounting->account('receivable');
            $fineIncome=$this->accounting->account('fine_income');

            $memberName=$due->subscription?->member?->user?->name
                ??$due->subscription?->member?->member_code
                ??'Member';

            $newAmount=round(
                (float)$due->amount+$fineAmount,
                2
            );

            $this->accounting->post([
                'transaction_date'=>now()->toDateString(),
                'type'=>'subscription_fine',
                'source_module'=>'subscription_fine',
                'source_id'=>$due->id,
                'reference_type'=>SubscriptionDue::class,
                'reference_id'=>$due->id,
                'description'=>"Late subscription fine - {$memberName} - {$due->period}",
                'user_id'=>$userId??auth()->id(),
                'entries'=>[
                    [
                        'account_id'=>$receivable->id,
                        'debit'=>$fineAmount,
                        'credit'=>0,
                        'description'=>'Subscription late fine receivable'
                    ],
                    [
                        'account_id'=>$fineIncome->id,
                        'debit'=>0,
                        'credit'=>$fineAmount,
                        'description'=>'Subscription late fine income'
                    ]
                ]
            ]);

            $due->update([
                'fine_amount'=>$fineAmount,
                'amount'=>$newAmount,
                'fine_applied_at'=>now(),
                'status'=>(float)$due->paid_amount>0
                    ?'partial'
                    :'overdue'
            ]);

            return $due->fresh([
                'subscription.plan',
                'subscription.member.user',
                'payments'
            ]);
        });
    }

    public function applyPendingLateFines(
        ?int $userId=null
    ): int{
        if(!$this->lateFineEnabled()){
            return 0;
        }

        $count=0;

        SubscriptionDue::query()
            ->whereIn('status',[
                'unpaid',
                'partial',
                'overdue'
            ])
            ->whereNull('fine_applied_at')
            ->chunkById(100,function($dues)use(&$count,$userId){
                foreach($dues as $due){
                    if(!$this->isFineApplicable($due)){
                        continue;
                    }

                    $updated=$this->applyLateFine(
                        $due,
                        $userId
                    );

                    if($updated->fine_applied_at){
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function calculateLateFine(
        SubscriptionDue $due
    ): float{
        $type=(string)setting(
            'subscription_fine_type',
            'fixed'
        );

        $value=max(
            (float)setting(
                'subscription_fine_value',
                0
            ),
            0
        );

        return match($type){
            'percentage'=>round(
                (float)$due->base_amount*($value/100),
                2
            ),
            'fixed'=>round($value,2),
            default=>0
        };
    }

    protected function isFineApplicable(
        SubscriptionDue $due
    ): bool{
        if($due->fine_applied_at){
            return false;
        }

        if(in_array($due->status,['paid','waived'],true)){
            return false;
        }

        $applyDay=max(
            1,
            min(
                (int)setting(
                    'subscription_fine_apply_day',
                    15
                ),
                31
            )
        );

        $period=Carbon::create(
            $due->year,
            $due->month,
            1
        );

        $effectiveDay=min(
            $applyDay,
            $period->daysInMonth
        );

        $fineDate=$period
            ->copy()
            ->day($effectiveDay)
            ->startOfDay();

        return now()
            ->startOfDay()
            ->gte($fineDate);
    }

    protected function resolvePaymentAccount(
        ?string $paymentMethod
    ){
        return match($paymentMethod){
            'bank',
            'mobile_banking',
            'online'=>$this->accounting->account('bank'),
            default=>$this->accounting->account('cash')
        };
    }

    protected function lateFineEnabled(): bool
    {
        return filter_var(
            setting(
                'subscription_fine_enabled',
                false
            ),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    protected function shareEnabled(): bool
    {
        return filter_var(
            setting(
                'share_enabled',
                false
            ),
            FILTER_VALIDATE_BOOLEAN
        );
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

        $number=$last
            ?((int)substr($last,-6))+1
            :1;

        do{
            $paymentNo=$prefix.str_pad(
                (string)$number,
                6,
                '0',
                STR_PAD_LEFT
            );

            $exists=SubscriptionPayment::query()
                ->where('payment_no',$paymentNo)
                ->exists();

            $number++;
        }while($exists);

        return $paymentNo;
    }
}