<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CommitteeMember;
use App\Models\Loan;
use App\Models\Member;
use App\Models\MemberCharge;
use App\Models\MemberExit;
use App\Models\MemberExitItem;
use App\Models\MemberExitNomineeAllocation;
use App\Models\MemberNominee;
use App\Models\MemberShare;
use App\Models\SubscriptionDue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberExitService
{
    public function __construct(
        protected AccountingService $accounting,
        protected NumberSequenceService $numbers,
        protected NotificationService $notifications,
        protected FinanceDashboardService $financeDashboard,
        protected MemberDashboardService $memberDashboard
    ){}

    public function initiate(
        Member $member,
        array $data,
        int $userId,
        bool $memberInitiated=false
    ): MemberExit{
        return DB::transaction(function()use(
            $member,
            $data,
            $userId,
            $memberInitiated
        ){
            $member=Member::query()
                ->whereKey($member->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array(
                $member->status,
                ['rejected','exited','deceased'],
                true
            )){
                throw ValidationException::withMessages([
                    'member'=>[
                        'This member cannot start a new exit process.'
                    ],
                ]);
            }

            if(
                $memberInitiated&&
                ($data['exit_type']??null)!=='resignation'
            ){
                throw ValidationException::withMessages([
                    'exit_type'=>[
                        'Member can only initiate a resignation request.'
                    ],
                ]);
            }

            $exists=MemberExit::query()
                ->where('member_id',$member->id)
                ->whereNotIn('status',[
                    'closed',
                    'rejected',
                    'cancelled',
                ])
                ->lockForUpdate()
                ->exists();

            if($exists){
                throw ValidationException::withMessages([
                    'member'=>[
                        'This member already has an active exit process.'
                    ],
                ]);
            }

            $exit=MemberExit::create([
                'exit_no'=>$this->generateNumber(),
                'member_id'=>$member->id,
                'exit_type'=>$data['exit_type'],
                'request_date'=>$data['request_date']
                    ??now()->toDateString(),
                'proposed_exit_date'=>
                    $data['proposed_exit_date']??null,
                'reason'=>trim($data['reason']),
                'status'=>'submitted',
                'member_initiated'=>$memberInitiated,
                'initiated_by'=>$userId,
            ]);

            $this->notify(
                $exit,
                'Exit Request Submitted',
                "Exit request {$exit->exit_no} has been submitted.",
                'info',
                $userId
            );

            $this->forgetCaches($member->id);

            return $this->freshExit($exit);
        });
    }

    public function startReview(
        MemberExit $exit,
        ?string $note,
        int $userId
    ): MemberExit{
        return DB::transaction(function()use(
            $exit,
            $note,
            $userId
        ){
            $exit=$this->lockExit($exit);

            if($exit->status!=='submitted'){
                throw ValidationException::withMessages([
                    'exit'=>[
                        'Only submitted exit requests can enter review.'
                    ],
                ]);
            }

            $exit->update([
                'status'=>'under_review',
                'review_note'=>$this->nullable($note),
                'reviewed_by'=>$userId,
                'reviewed_at'=>now(),
            ]);

            return $this->assessLocked(
                $exit,
                $userId
            );
        });
    }

    public function assess(
        MemberExit $exit,
        int $userId
    ): MemberExit{
        return DB::transaction(function()use(
            $exit,
            $userId
        ){
            $exit=$this->lockExit($exit);

            if(!in_array(
                $exit->status,
                [
                    'submitted',
                    'under_review',
                    'liabilities_pending',
                    'ready_for_approval',
                ],
                true
            )){
                throw ValidationException::withMessages([
                    'exit'=>[
                        'Financial assessment is not allowed at this stage.'
                    ],
                ]);
            }

            return $this->assessLocked(
                $exit,
                $userId
            );
        });
    }

    protected function assessLocked(
        MemberExit $exit,
        int $userId
    ): MemberExit{
        $member=Member::query()
            ->whereKey($exit->member_id)
            ->lockForUpdate()
            ->firstOrFail();

        $exit->items()->delete();

        $subscriptionDue=0;
        $chargeDue=0;
        $loanDue=0;
        $shareRefund=0;
        $blockers=0;

        /*
        |--------------------------------------------------------------------------
        | Subscription dues
        |--------------------------------------------------------------------------
        */

        $subscriptionDues=SubscriptionDue::query()
            ->whereHas(
                'subscription',
                fn($q)=>$q->where(
                    'member_id',
                    $member->id
                )
            )
            ->whereNotIn(
                'status',
                ['paid','waived']
            )
            ->get();

        foreach($subscriptionDues as $due){
            $amount=round(
                (float)$due->outstanding,
                2
            );

            if($amount<=0){
                continue;
            }

            $subscriptionDue+=$amount;
            $blockers++;

            $this->addItem(
                $exit,
                'subscription_due',
                'liability',
                SubscriptionDue::class,
                $due->id,
                "Subscription due {$due->period}",
                $amount,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Charges
        |--------------------------------------------------------------------------
        */

        $charges=MemberCharge::query()
            ->where('member_id',$member->id)
            ->whereNotIn(
                'status',
                ['paid','waived','cancelled']
            )
            ->get();

        foreach($charges as $charge){
            $amount=round(
                (float)$charge->outstanding,
                2
            );

            if($amount<=0){
                continue;
            }

            $chargeDue+=$amount;
            $blockers++;

            $this->addItem(
                $exit,
                'charge',
                'liability',
                MemberCharge::class,
                $charge->id,
                "Outstanding charge {$charge->charge_no}",
                $amount,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Loans
        |--------------------------------------------------------------------------
        */

        $loans=Loan::query()
            ->where('member_id',$member->id)
            ->whereIn('status',[
                'approved',
                'active',
                'overdue',
                'defaulted',
            ])
            ->withSum(
                'repayments as repaid_total',
                'total_amount'
            )
            ->get();

        foreach($loans as $loan){
            if($loan->status==='approved'){
                $blockers++;

                $this->addItem(
                    $exit,
                    'loan',
                    'process',
                    Loan::class,
                    $loan->id,
                    "Approved loan {$loan->loan_no} must be cancelled before exit.",
                    0,
                    true
                );

                continue;
            }

            $outstanding=max(
                round(
                    (float)$loan->total_payable-
                    (float)($loan->repaid_total??0),
                    2
                ),
                0
            );

            if($outstanding<=0){
                continue;
            }

            $loanDue+=$outstanding;
            $blockers++;

            $this->addItem(
                $exit,
                'loan',
                'liability',
                Loan::class,
                $loan->id,
                "Outstanding loan {$loan->loan_no}",
                $outstanding,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Active shares = member entitlement
        |--------------------------------------------------------------------------
        */

        $shares=MemberShare::query()
            ->where('member_id',$member->id)
            ->where('status','active')
            ->get();

        foreach($shares as $share){
            $amount=round(
                (float)$share->purchase_amount,
                2
            );

            $shareRefund+=$amount;

            $this->addItem(
                $exit,
                'share',
                'entitlement',
                MemberShare::class,
                $share->id,
                "Share refund {$share->share_no}",
                $amount,
                false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pending share purchases are unresolved processes
        |--------------------------------------------------------------------------
        */

        $pendingShares=MemberShare::query()
            ->where('member_id',$member->id)
            ->where('status','pending')
            ->get();

        foreach($pendingShares as $share){
            $blockers++;

            $this->addItem(
                $exit,
                'pending_share',
                'process',
                MemberShare::class,
                $share->id,
                "Pending share purchase {$share->share_no} must be resolved.",
                0,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Committee position
        |--------------------------------------------------------------------------
        |
        | Normal resignation/removal requires explicit replacement/removal.
        | Death case will be auto-ended during closure.
        |
        */

        if($exit->exit_type!=='death'){
            $committeeMemberships=
                CommitteeMember::query()
                    ->where('member_id',$member->id)
                    ->where('status','active')
                    ->with('position:id,name')
                    ->get();

            foreach($committeeMemberships as $membership){
                $blockers++;

                $this->addItem(
                    $exit,
                    'committee_position',
                    'process',
                    CommitteeMember::class,
                    $membership->id,
                    'Active committee position: '.
                    ($membership->position?->name??'Committee Member'),
                    0,
                    true
                );
            }
        }

        $totalLiabilities=round(
            $subscriptionDue+
            $chargeDue+
            $loanDue,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Do not auto-net financial sub-ledgers
        |--------------------------------------------------------------------------
        |
        | Liabilities must be cleared through their own payment flows.
        | Therefore final payable is only released once blockers = 0.
        |
        */

        $netSettlement=$blockers===0
            ?round($shareRefund,2)
            :0;

        $status=$blockers>0
            ?'liabilities_pending'
            :'ready_for_approval';

        $exit->update([
            'subscription_due'=>round($subscriptionDue,2),
            'charge_due'=>round($chargeDue,2),
            'loan_due'=>round($loanDue,2),
            'total_liabilities'=>$totalLiabilities,
            'share_refund'=>round($shareRefund,2),
            'net_settlement_amount'=>$netSettlement,
            'blocking_items_count'=>$blockers,
            'status'=>$status,
            'reviewed_by'=>$exit->reviewed_by??$userId,
            'reviewed_at'=>$exit->reviewed_at??now(),
        ]);

        $this->forgetCaches($member->id);

        return $this->freshExit($exit);
    }

    public function approve(
        MemberExit $exit,
        int $userId
    ): MemberExit{
        return DB::transaction(function()use(
            $exit,
            $userId
        ){
            $exit=$this->lockExit($exit);

            /*
            |--------------------------------------------------------------------------
            | Refresh financial position
            |--------------------------------------------------------------------------
            */

            $exit=$this->assessLocked(
                $exit,
                $userId
            );

            if(
                $exit->status!=='ready_for_approval'||
                $exit->blocking_items_count>0||
                (float)$exit->total_liabilities>0
            ){
                throw ValidationException::withMessages([
                    'exit'=>[
                        'All financial and operational blockers must be resolved before approval.'
                    ],
                ]);
            }

            if(
                $exit->exit_type==='death'&&
                (float)$exit->share_refund>0
            ){
                $this->buildDeathNomineeAllocations(
                    $exit
                );
            }

            $exit->update([
                'status'=>'approved',
                'approved_by'=>$userId,
                'approved_at'=>now(),
                'rejection_reason'=>null,
                'rejected_by'=>null,
                'rejected_at'=>null,
            ]);

            $this->notify(
                $exit,
                'Exit Request Approved',
                "Exit request {$exit->exit_no} has been approved.",
                'success',
                $userId
            );

            return $this->freshExit($exit);
        });
    }

    public function reject(
        MemberExit $exit,
        string $reason,
        int $userId
    ): MemberExit{
        return DB::transaction(function()use(
            $exit,
            $reason,
            $userId
        ){
            $exit=$this->lockExit($exit);

            if(!in_array(
                $exit->status,
                [
                    'submitted',
                    'under_review',
                    'liabilities_pending',
                    'ready_for_approval',
                ],
                true
            )){
                throw ValidationException::withMessages([
                    'exit'=>[
                        'This exit request can no longer be rejected.'
                    ],
                ]);
            }

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'rejection_reason'=>[
                        'Rejection reason is required.'
                    ],
                ]);
            }

            $exit->update([
                'status'=>'rejected',
                'rejection_reason'=>$reason,
                'rejected_by'=>$userId,
                'rejected_at'=>now(),
            ]);

            $this->notify(
                $exit,
                'Exit Request Rejected',
                "Exit request {$exit->exit_no} has been rejected.",
                'warning',
                $userId
            );

            $this->forgetCaches($exit->member_id);

            return $this->freshExit($exit);
        });
    }

    public function cancel(
        MemberExit $exit,
        int $userId,
        bool $memberAction=false
    ): MemberExit{
        return DB::transaction(function()use(
            $exit,
            $userId,
            $memberAction
        ){
            $exit=$this->lockExit($exit);

            if(!in_array(
                $exit->status,
                [
                    'submitted',
                    'under_review',
                    'liabilities_pending',
                    'ready_for_approval',
                ],
                true
            )){
                throw ValidationException::withMessages([
                    'exit'=>[
                        'This exit process can no longer be cancelled.'
                    ],
                ]);
            }

            if(
                $memberAction&&
                !$exit->member_initiated
            ){
                throw ValidationException::withMessages([
                    'exit'=>[
                        'Member cannot cancel an admin-initiated exit process.'
                    ],
                ]);
            }

            $exit->update([
                'status'=>'cancelled',
            ]);

            $this->forgetCaches($exit->member_id);

            return $this->freshExit($exit);
        });
    }

    public function settle(
        MemberExit $exit,
        array $data,
        int $userId
    ): MemberExit{
        return DB::transaction(function()use(
            $exit,
            $data,
            $userId
        ){
            $exit=$this->lockExit($exit);

            if($exit->status!=='approved'){
                throw ValidationException::withMessages([
                    'exit'=>[
                        'Only approved exit processes can be settled.'
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Re-check current blockers
            |--------------------------------------------------------------------------
            */

            $current=$this->currentBlockerSummary(
                $exit->member_id,
                $exit->exit_type
            );

            if($current['count']>0){
                throw ValidationException::withMessages([
                    'exit'=>[
                        'New or unresolved liabilities exist. Run assessment again before settlement.'
                    ],
                ]);
            }

            $member=Member::query()
                ->with('user')
                ->whereKey($exit->member_id)
                ->lockForUpdate()
                ->firstOrFail();

            $shares=MemberShare::query()
                ->where('member_id',$member->id)
                ->where('status','active')
                ->lockForUpdate()
                ->get();

            $refund=round(
                (float)$shares->sum('purchase_amount'),
                2
            );

            $transaction=null;
            $account=null;

            if($refund>0){
                $account=$this->cashBankAccount(
                    (int)$data['payout_account_id']
                );

                $memberEquity=$this->accounting
                    ->account('member_equity');

                /*
                |--------------------------------------------------------------------------
                | Share capital refund
                |--------------------------------------------------------------------------
                |
                | Dr Member Share Capital
                | Cr Cash / Bank
                |
                | NOT an expense.
                |
                */

                $transaction=$this->accounting->post([
                    'idempotency_key'=>
                        "member-exit:settlement:{$exit->id}",

                    'transaction_date'=>
                        $data['settlement_date'],

                    'type'=>'member_exit_settlement',
                    'source_module'=>'member_exit',
                    'source_id'=>$exit->id,
                    'reference_type'=>MemberExit::class,
                    'reference_id'=>$exit->id,

                    'description'=>
                        "Member exit settlement {$exit->exit_no}",

                    'user_id'=>$userId,

                    'entries'=>[
                        [
                            'account_id'=>$memberEquity->id,
                            'debit'=>$refund,
                            'credit'=>0,
                            'description'=>
                                "Member share capital refund {$exit->exit_no}",
                        ],
                        [
                            'account_id'=>$account->id,
                            'debit'=>0,
                            'credit'=>$refund,
                            'description'=>
                                "Exit settlement payout {$exit->exit_no}",
                        ],
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Retire member shares
            |--------------------------------------------------------------------------
            */

            MemberShare::query()
                ->where('member_id',$member->id)
                ->where('status','active')
                ->update([
                    'status'=>'retired',
                    'notes'=>DB::raw(
                        "CONCAT(COALESCE(notes,''), '\nRetired through exit {$exit->exit_no}')"
                    ),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Stop subscription generation
            |--------------------------------------------------------------------------
            */

            $member->subscriptions()
                ->where('is_active',true)
                ->update([
                    'is_active'=>false,
                    'end_date'=>$data['settlement_date'],
                ]);

            /*
            |--------------------------------------------------------------------------
            | Death: end active committee positions automatically
            |--------------------------------------------------------------------------
            */

            if($exit->exit_type==='death'){
                CommitteeMember::query()
                    ->where('member_id',$member->id)
                    ->where('status','active')
                    ->update([
                        'status'=>'removed',
                        'end_date'=>$data['settlement_date'],
                        'notes'=>DB::raw(
                            "CONCAT(COALESCE(notes,''), '\nClosed due to member death')"
                        ),
                    ]);
            }

            $memberStatus=$exit->exit_type==='death'
                ?'deceased'
                :'exited';

            $member->update([
                'status'=>$memberStatus,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Disable member login
            |--------------------------------------------------------------------------
            */

            if($member->user){
                $member->user->update([
                    'is_active'=>false,
                ]);
            }

            $exit->items()
                ->where('direction','entitlement')
                ->update(['status'=>'settled']);

            $exit->update([
                'share_refund'=>$refund,
                'net_settlement_amount'=>$refund,
                'payout_account_id'=>$account?->id,
                'settlement_transaction_id'=>$transaction?->id,
                'settled_by'=>$userId,
                'settled_at'=>now(),
                'status'=>'settled',
            ]);

            $this->forgetCaches($member->id);

            return $this->freshExit($exit);
        });
    }

    public function close(
        MemberExit $exit,
        int $userId
    ): MemberExit{
        return DB::transaction(function()use(
            $exit,
            $userId
        ){
            $exit=$this->lockExit($exit);

            if($exit->status!=='settled'){
                throw ValidationException::withMessages([
                    'exit'=>[
                        'Settlement must be completed before closing membership.'
                    ],
                ]);
            }

            $exit->update([
                'status'=>'closed',
                'closed_by'=>$userId,
                'closed_at'=>now(),
            ]);

            $this->forgetCaches($exit->member_id);

            return $this->freshExit($exit);
        });
    }

    public function statistics(): array
    {
        return Cache::remember(
            'member-exits:statistics',
            now()->addMinutes(5),
            fn()=>[
                'total'=>MemberExit::count(),

                'pending'=>MemberExit::whereIn(
                    'status',
                    [
                        'submitted',
                        'under_review',
                        'liabilities_pending',
                        'ready_for_approval',
                    ]
                )->count(),

                'approved'=>MemberExit::where(
                    'status',
                    'approved'
                )->count(),

                'closed'=>MemberExit::where(
                    'status',
                    'closed'
                )->count(),

                'death'=>MemberExit::where(
                    'exit_type',
                    'death'
                )->count(),
            ]
        );
    }

    protected function buildDeathNomineeAllocations(
        MemberExit $exit
    ): void{
        $nominees=MemberNominee::query()
            ->where('member_id',$exit->member_id)
            ->where('is_active',true)
            ->where(
                'verification_status',
                'verified'
            )
            ->orderBy('priority')
            ->lockForUpdate()
            ->get();

        if($nominees->isEmpty()){
            throw ValidationException::withMessages([
                'nominee'=>[
                    'Death settlement requires verified active nominee information.'
                ],
            ]);
        }

        $totalPercentage=round(
            (float)$nominees->sum(
                'allocation_percentage'
            ),
            2
        );

        if(abs($totalPercentage-100)>0.009){
            throw ValidationException::withMessages([
                'nominee'=>[
                    'Verified active nominee allocation must total exactly 100% before death settlement approval.'
                ],
            ]);
        }

        $exit->nomineeAllocations()->delete();

        $refund=round(
            (float)$exit->share_refund,
            2
        );

        $allocated=0;
        $lastId=$nominees->last()->id;

        foreach($nominees as $nominee){
            $amount=$nominee->id===$lastId
                ?round($refund-$allocated,2)
                :round(
                    $refund*
                    ((float)$nominee->allocation_percentage/100),
                    2
                );

            $allocated+=
                $nominee->id===$lastId
                    ?0
                    :$amount;

            MemberExitNomineeAllocation::create([
                'member_exit_id'=>$exit->id,
                'member_nominee_id'=>$nominee->id,
                'allocation_percentage'=>
                    $nominee->allocation_percentage,
                'amount'=>$amount,
            ]);
        }
    }

    protected function currentBlockerSummary(
        int $memberId,
        string $exitType
    ): array{
        $subscription=SubscriptionDue::query()
            ->whereHas(
                'subscription',
                fn($q)=>$q->where(
                    'member_id',
                    $memberId
                )
            )
            ->whereNotIn(
                'status',
                ['paid','waived']
            )
            ->get()
            ->filter(
                fn($due)=>
                    $due->outstanding>0
            )
            ->count();

        $charges=MemberCharge::query()
            ->where('member_id',$memberId)
            ->whereNotIn(
                'status',
                ['paid','waived','cancelled']
            )
            ->get()
            ->filter(
                fn($charge)=>
                    $charge->outstanding>0
            )
            ->count();

        $loans=Loan::query()
            ->where('member_id',$memberId)
            ->whereIn('status',[
                'approved',
                'active',
                'overdue',
                'defaulted',
            ])
            ->count();

        $pendingShares=MemberShare::query()
            ->where('member_id',$memberId)
            ->where('status','pending')
            ->count();

        $committee=0;

        if($exitType!=='death'){
            $committee=CommitteeMember::query()
                ->where('member_id',$memberId)
                ->where('status','active')
                ->count();
        }

        return[
            'count'=>
                $subscription+
                $charges+
                $loans+
                $pendingShares+
                $committee,
        ];
    }

    protected function addItem(
        MemberExit $exit,
        string $category,
        string $direction,
        ?string $referenceType,
        ?int $referenceId,
        string $description,
        float $amount,
        bool $blocking
    ): MemberExitItem{
        return $exit->items()->create([
            'category'=>$category,
            'direction'=>$direction,
            'reference_type'=>$referenceType,
            'reference_id'=>$referenceId,
            'description'=>$description,
            'amount'=>round($amount,2),
            'is_blocking'=>$blocking,
            'status'=>$blocking
                ?'pending'
                :'cleared',
        ]);
    }

    protected function cashBankAccount(
        int $accountId
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->where('is_active',true)
            ->whereIn(
                'sub_type',
                ['cash','bank']
            )
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'payout_account_id'=>[
                    'A valid active Cash or Bank account is required.'
                ],
            ]);
        }

        return $account;
    }

    protected function lockExit(
        MemberExit $exit
    ): MemberExit{
        return MemberExit::query()
            ->whereKey($exit->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function freshExit(
        MemberExit $exit
    ): MemberExit{
        return $exit->fresh([
            'member.user:id,name,email,is_active',
            'initiator:id,name',
            'reviewer:id,name',
            'approver:id,name',
            'payoutAccount:id,code,name,sub_type',
            'settlementTransaction.entries.account',
            'items'=>fn($q)=>$q
                ->orderByDesc('is_blocking')
                ->orderBy('category'),

            'nomineeAllocations.nominee',
        ]);
    }

    protected function generateNumber(): string
    {
        return $this->numbers->next(
            key:'member-exit',
            prefix:'EXIT-',
            digits:6,
            initialValue:function(){
                $last=MemberExit::query()
                    ->where(
                        'exit_no',
                        'like',
                        'EXIT-%'
                    )
                    ->orderByDesc('id')
                    ->value('exit_no');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }

    protected function notify(
        MemberExit $exit,
        string $title,
        string $message,
        string $type,
        int $senderId
    ): void{
        $exit->loadMissing('member.user');

        $userId=$exit->member?->user_id;

        if(!$userId){
            return;
        }

        DB::afterCommit(function()use(
            $userId,
            $title,
            $message,
            $type,
            $senderId
        ){
            $this->notifications->sendSystem([
                'title'=>$title,
                'message'=>$message,
                'type'=>$type,
                'audience_type'=>'users',
                'user_ids'=>[$userId],
                'action_url'=>route(
                    'member.exit'
                ),
                'sent_by'=>$senderId,
            ]);
        });
    }

    protected function forgetCaches(
        int $memberId
    ): void{
        DB::afterCommit(function()use($memberId){
            Cache::forget('member-exits:statistics');
            Cache::forget(
                "member-exits:member:{$memberId}"
            );

            $this->financeDashboard
                ->forgetCache();

            $this->memberDashboard
                ->forgetFinancialCache();
        });
    }

    protected function nullable(
        mixed $value
    ): ?string{
        if($value===null){
            return null;
        }

        $value=trim(
            (string)$value
        );

        return $value===''
            ?null
            :$value;
    }
}