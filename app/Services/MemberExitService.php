<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CommitteeMember;
use App\Models\Loan;
use App\Models\Member;
use App\Models\MemberCharge;
use App\Models\MemberExit;
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
        return DB::transaction(function()use($member,$data,$userId,$memberInitiated){
            $member=Member::query()
                ->select(['id','user_id','member_code','status'])
                ->whereKey($member->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($member->status,['rejected','exited','deceased'],true)){
                throw ValidationException::withMessages([
                    'member'=>['This member cannot start a new exit process.'],
                ]);
            }

            if($memberInitiated&&($data['exit_type']??null)!=='resignation'){
                throw ValidationException::withMessages([
                    'exit_type'=>['Member can only initiate a resignation request.'],
                ]);
            }

            $exists=MemberExit::query()
                ->where('member_id',$member->id)
                ->whereNotIn('status',['closed','rejected','cancelled'])
                ->exists();

            if($exists){
                throw ValidationException::withMessages([
                    'member'=>['This member already has an active exit process.'],
                ]);
            }

            $requestDate=$data['request_date']??now()->toDateString();
            $proposedExitDate=$data['proposed_exit_date']??null;

            if($proposedExitDate&&$proposedExitDate<$requestDate){
                throw ValidationException::withMessages([
                    'proposed_exit_date'=>[
                        'Proposed exit date cannot be before the request date.'
                    ],
                ]);
            }

            $exit=MemberExit::create([
                'exit_no'=>$this->generateNumber(),
                'member_id'=>$member->id,
                'exit_type'=>$data['exit_type'],
                'request_date'=>$requestDate,
                'proposed_exit_date'=>$proposedExitDate,
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
        return DB::transaction(function()use($exit,$note,$userId){
            $exit=$this->lockExit($exit);

            if($exit->status!=='submitted'){
                throw ValidationException::withMessages([
                    'exit'=>['Only submitted exit requests can enter review.'],
                ]);
            }

            $exit->update([
                'status'=>'under_review',
                'review_note'=>$this->nullable($note),
                'reviewed_by'=>$userId,
                'reviewed_at'=>now(),
            ]);

            return $this->assessLocked($exit,$userId);
        });
    }

    public function assess(
        MemberExit $exit,
        int $userId
    ): MemberExit{
        return DB::transaction(function()use($exit,$userId){
            $exit=$this->lockExit($exit);

            if(!in_array($exit->status,[
                'submitted',
                'under_review',
                'liabilities_pending',
                'ready_for_approval',
                'approved',
            ],true)){
                throw ValidationException::withMessages([
                    'exit'=>['Financial assessment is not allowed at this stage.'],
                ]);
            }

            $wasApproved=$exit->status==='approved';
            $approvedBy=$exit->approved_by;
            $approvedAt=$exit->approved_at;

            $exit=$this->assessLocked($exit,$userId);

            if($wasApproved){
                if((int)$exit->blocking_items_count===0){
                    if($exit->exit_type==='death'){
                        if((float)$exit->share_refund>0){
                            $this->buildDeathNomineeAllocations($exit);
                        }else{
                            DB::table('member_exit_nominee_allocations')
                                ->where('member_exit_id',$exit->id)
                                ->delete();
                        }
                    }

                    $exit->update([
                        'status'=>'approved',
                        'approved_by'=>$approvedBy,
                        'approved_at'=>$approvedAt,
                    ]);
                }else{
                    if($exit->exit_type==='death'){
                        DB::table('member_exit_nominee_allocations')
                            ->where('member_exit_id',$exit->id)
                            ->delete();
                    }

                    $exit->update([
                        'approved_by'=>null,
                        'approved_at'=>null,
                    ]);
                }

                $exit=$this->freshExit($exit);
            }

            return $exit;
        });
    }

    protected function assessLocked(
        MemberExit $exit,
        int $userId
    ): MemberExit{
        $member=Member::query()
            ->select(['id','status'])
            ->whereKey($exit->member_id)
            ->lockForUpdate()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Delete old assessment items
        |--------------------------------------------------------------------------
        */

        DB::table('member_exit_items')
            ->where('member_exit_id',$exit->id)
            ->delete();

        $subscriptionDue=0.0;
        $chargeDue=0.0;
        $loanDue=0.0;
        $shareRefund=0.0;
        $blockers=0;
        $items=[];

        /*
        |--------------------------------------------------------------------------
        | Subscription dues
        |--------------------------------------------------------------------------
        */

        $subscriptionDues=SubscriptionDue::query()
            ->select([
                'subscription_dues.id',
                'subscription_dues.year',
                'subscription_dues.month',
                'subscription_dues.amount',
                'subscription_dues.paid_amount',
            ])
            ->join(
                'member_subscriptions as ms',
                'ms.id',
                '=',
                'subscription_dues.member_subscription_id'
            )
            ->where('ms.member_id',$member->id)
            ->whereNotIn('subscription_dues.status',['paid','waived'])
            ->whereColumn(
                'subscription_dues.amount',
                '>',
                'subscription_dues.paid_amount'
            )
            ->get();

        foreach($subscriptionDues as $due){
            $amount=max(
                round(
                    (float)$due->amount-(float)$due->paid_amount,
                    2
                ),
                0
            );

            if($amount<=0){
                continue;
            }

            $subscriptionDue+=$amount;
            $blockers++;

            $items[]=$this->itemData(
                exitId:$exit->id,
                category:'subscription_due',
                direction:'liability',
                referenceType:SubscriptionDue::class,
                referenceId:(int)$due->id,
                description:sprintf(
                    'Subscription due %04d-%02d',
                    $due->year,
                    $due->month
                ),
                amount:$amount,
                blocking:true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Charges
        |--------------------------------------------------------------------------
        */

        $charges=MemberCharge::query()
            ->select([
                'id',
                'charge_no',
                'amount',
                'paid_amount',
            ])
            ->where('member_id',$member->id)
            ->whereNotIn('status',['paid','waived','cancelled'])
            ->whereColumn('amount','>','paid_amount')
            ->get();

        foreach($charges as $charge){
            $amount=max(
                round(
                    (float)$charge->amount-(float)$charge->paid_amount,
                    2
                ),
                0
            );

            if($amount<=0){
                continue;
            }

            $chargeDue+=$amount;
            $blockers++;

            $items[]=$this->itemData(
                exitId:$exit->id,
                category:'charge',
                direction:'liability',
                referenceType:MemberCharge::class,
                referenceId:(int)$charge->id,
                description:"Outstanding charge {$charge->charge_no}",
                amount:$amount,
                blocking:true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Loans
        |--------------------------------------------------------------------------
        */

        $loans=Loan::query()
            ->select([
                'id',
                'loan_no',
                'status',
                'total_payable',
            ])
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

                $items[]=$this->itemData(
                    exitId:$exit->id,
                    category:'loan',
                    direction:'process',
                    referenceType:Loan::class,
                    referenceId:(int)$loan->id,
                    description:"Approved loan {$loan->loan_no} must be cancelled before exit.",
                    amount:0,
                    blocking:true
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

            $items[]=$this->itemData(
                exitId:$exit->id,
                category:'loan',
                direction:'liability',
                referenceType:Loan::class,
                referenceId:(int)$loan->id,
                description:"Outstanding loan {$loan->loan_no}",
                amount:$outstanding,
                blocking:true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Shares
        |--------------------------------------------------------------------------
        | Active  = refundable entitlement
        | Pending = unresolved process/blocker
        |--------------------------------------------------------------------------
        */

        $shares=MemberShare::query()
            ->select([
                'id',
                'share_no',
                'status',
                'purchase_amount',
            ])
            ->where('member_id',$member->id)
            ->whereIn('status',['active','pending'])
            ->get();

        foreach($shares as $share){
            if($share->status==='active'){
                $amount=max(
                    round((float)$share->purchase_amount,2),
                    0
                );

                $shareRefund+=$amount;

                $items[]=$this->itemData(
                    exitId:$exit->id,
                    category:'share',
                    direction:'entitlement',
                    referenceType:MemberShare::class,
                    referenceId:(int)$share->id,
                    description:"Share refund {$share->share_no}",
                    amount:$amount,
                    blocking:false
                );

                continue;
            }

            $blockers++;

            $items[]=$this->itemData(
                exitId:$exit->id,
                category:'pending_share',
                direction:'process',
                referenceType:MemberShare::class,
                referenceId:(int)$share->id,
                description:"Pending share purchase {$share->share_no} must be resolved.",
                amount:0,
                blocking:true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Bulk insert assessment items
        |--------------------------------------------------------------------------
        | Previously each item used its own INSERT query.
        |--------------------------------------------------------------------------
        */

        if(!empty($items)){
            foreach(array_chunk($items,500) as $chunk){
                DB::table('member_exit_items')->insert($chunk);
            }
        }

        $subscriptionDue=round($subscriptionDue,2);
        $chargeDue=round($chargeDue,2);
        $loanDue=round($loanDue,2);
        $shareRefund=round($shareRefund,2);

        $totalLiabilities=round(
            $subscriptionDue+
            $chargeDue+
            $loanDue,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Financial liabilities are not auto-netted
        |--------------------------------------------------------------------------
        */

        $netSettlement=$blockers===0
            ?$shareRefund
            :0;

        $status=$blockers>0
            ?'liabilities_pending'
            :'ready_for_approval';

        $exit->update([
            'subscription_due'=>$subscriptionDue,
            'charge_due'=>$chargeDue,
            'loan_due'=>$loanDue,
            'total_liabilities'=>$totalLiabilities,
            'share_refund'=>$shareRefund,
            'net_settlement_amount'=>$netSettlement,
            'blocking_items_count'=>$blockers,
            'status'=>$status,
            'reviewed_by'=>$exit->reviewed_by??$userId,
            'reviewed_at'=>$exit->reviewed_at??now(),
        ]);

        $this->forgetCaches($member->id);

        return $this->freshExit($exit);
    }

    /**
     * Finalize a member-exit approval. Called by
     * ApprovalService::executeApprovedAction() once the module=MemberExit,
     * action=request workflow has been fully signed off. $decisionData is
     * accepted for consistency with the other modules but this module
     * doesn't currently need any approver-supplied fields.
     */
    public function finalizeApproval(
        MemberExit $exit,
        array $decisionData,
        int $userId
    ): MemberExit{
        return DB::transaction(function()use($exit,$userId){
            $exit=$this->lockExit($exit);

            /*
            |--------------------------------------------------------------------------
            | Re-assess before approval
            |--------------------------------------------------------------------------
            */

            $exit=$this->assessLocked($exit,$userId);

            if(
                $exit->status!=='ready_for_approval'||
                (int)$exit->blocking_items_count>0||
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
                $this->buildDeathNomineeAllocations($exit);
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

    /**
     * Finalize a member-exit rejection. Called by
     * ApprovalService::executeRejectedAction().
     */
    public function finalizeRejection(
        MemberExit $exit,
        string $reason,
        int $userId
    ): MemberExit{
        return DB::transaction(function()use($exit,$reason,$userId){
            $exit=$this->lockExit($exit);

            if(!in_array($exit->status,[
                'submitted',
                'under_review',
                'liabilities_pending',
                'ready_for_approval',
            ],true)){
                throw ValidationException::withMessages([
                    'exit'=>['This exit request can no longer be rejected.'],
                ]);
            }

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'rejection_reason'=>['Rejection reason is required.'],
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
        return DB::transaction(function()use($exit,$userId,$memberAction){
            $exit=$this->lockExit($exit);

            if(!in_array($exit->status,[
                'submitted',
                'under_review',
                'liabilities_pending',
                'ready_for_approval',
            ],true)){
                throw ValidationException::withMessages([
                    'exit'=>['This exit process can no longer be cancelled.'],
                ]);
            }

            if($memberAction&&!$exit->member_initiated){
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
        return DB::transaction(function()use($exit,$data,$userId){
            $exit=$this->lockExit($exit);

            if($exit->status!=='approved'){
                throw ValidationException::withMessages([
                    'exit'=>['Only approved exit processes can be settled.'],
                ]);
            }

            $requestDate=$exit->request_date?->toDateString();

            if($requestDate&&$data['settlement_date']<$requestDate){
                throw ValidationException::withMessages([
                    'settlement_date'=>[
                        'Settlement date cannot be before the exit request date.'
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Re-check all current blockers
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
                ->select([
                    'id',
                    'user_id',
                    'status',
                ])
                ->whereKey($exit->member_id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Lock active shares before refund calculation
            |--------------------------------------------------------------------------
            */

            $shares=MemberShare::query()
                ->select([
                    'id',
                    'purchase_amount',
                ])
                ->where('member_id',$member->id)
                ->where('status','active')
                ->lockForUpdate()
                ->get();

            $refund=round(
                (float)$shares->sum('purchase_amount'),
                2
            );

            if($exit->exit_type==='death'){
                if($refund>0){
                    $exit->share_refund=$refund;
                    $this->buildDeathNomineeAllocations($exit);
                }else{
                    DB::table('member_exit_nominee_allocations')
                        ->where('member_exit_id',$exit->id)
                        ->delete();
                }
            }

            $transaction=null;
            $account=null;

            /*
            |--------------------------------------------------------------------------
            | Post accounting settlement
            |--------------------------------------------------------------------------
            */

            if($refund>0){
                $account=$this->cashBankAccount(
                    (int)$data['payout_account_id']
                );

                $memberEquity=$this->accounting
                    ->account('member_equity');

                /*
                |--------------------------------------------------------------------------
                | Dr Member Share Capital
                | Cr Cash / Bank
                |--------------------------------------------------------------------------
                */

                $transaction=$this->accounting->post([
                    'idempotency_key'=>"member-exit:settlement:{$exit->id}",
                    'transaction_date'=>$data['settlement_date'],
                    'type'=>'member_exit_settlement',
                    'source_module'=>'member_exit',
                    'source_id'=>$exit->id,
                    'reference_type'=>MemberExit::class,
                    'reference_id'=>$exit->id,
                    'description'=>"Member exit settlement {$exit->exit_no}",
                    'user_id'=>$userId,

                    'entries'=>[
                        [
                            'account_id'=>$memberEquity->id,
                            'debit'=>$refund,
                            'credit'=>0,
                            'description'=>"Member share capital refund {$exit->exit_no}",
                        ],
                        [
                            'account_id'=>$account->id,
                            'debit'=>0,
                            'credit'=>$refund,
                            'description'=>"Exit settlement payout {$exit->exit_no}",
                        ],
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Retire active shares
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
            | Disable future subscriptions
            |--------------------------------------------------------------------------
            */

            DB::table('member_subscriptions')
                ->where('member_id',$member->id)
                ->where('is_active',true)
                ->update([
                    'is_active'=>false,
                    'end_date'=>$data['settlement_date'],
                    'updated_at'=>now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Death: automatically close active committee positions
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

            /*
            |--------------------------------------------------------------------------
            | Member status
            |--------------------------------------------------------------------------
            */

            $member->update([
                'status'=>$exit->exit_type==='death'
                    ?'deceased'
                    :'exited',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Disable login
            |--------------------------------------------------------------------------
            | Direct UPDATE avoids SELECTing the User model first.
            | If User model observers are required, replace this with model update.
            |--------------------------------------------------------------------------
            */

            if($member->user_id){
                DB::table('users')
                    ->where('id',$member->user_id)
                    ->update([
                        'is_active'=>false,
                        'updated_at'=>now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Mark entitlement items settled
            |--------------------------------------------------------------------------
            */

            DB::table('member_exit_items')
                ->where('member_exit_id',$exit->id)
                ->where('direction','entitlement')
                ->update([
                    'status'=>'settled',
                    'updated_at'=>now(),
                ]);

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
        return DB::transaction(function()use($exit,$userId){
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

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    | 5+ COUNT queries -> 1 aggregate query.
    |--------------------------------------------------------------------------
    */

    public function statistics(): array
    {
        return Cache::remember(
            'member-exits:statistics',
            now()->addMinutes(5),
            function(){
                $row=MemberExit::query()
                    ->selectRaw("
                        COUNT(*) AS total,

                        SUM(
                            CASE
                                WHEN status IN (
                                    'submitted',
                                    'under_review',
                                    'liabilities_pending',
                                    'ready_for_approval'
                                )
                                THEN 1 ELSE 0
                            END
                        ) AS pending,

                        SUM(
                            CASE
                                WHEN status='approved'
                                THEN 1 ELSE 0
                            END
                        ) AS approved,

                        SUM(
                            CASE
                                WHEN status='settled'
                                THEN 1 ELSE 0
                            END
                        ) AS settled,

                        SUM(
                            CASE
                                WHEN status='closed'
                                THEN 1 ELSE 0
                            END
                        ) AS closed,

                        SUM(
                            CASE
                                WHEN status='rejected'
                                THEN 1 ELSE 0
                            END
                        ) AS rejected,

                        SUM(
                            CASE
                                WHEN status='cancelled'
                                THEN 1 ELSE 0
                            END
                        ) AS cancelled,

                        SUM(
                            CASE
                                WHEN exit_type='death'
                                THEN 1 ELSE 0
                            END
                        ) AS death
                    ")
                    ->first();

                return[
                    'total'=>(int)($row->total??0),
                    'pending'=>(int)($row->pending??0),
                    'approved'=>(int)($row->approved??0),
                    'settled'=>(int)($row->settled??0),
                    'closed'=>(int)($row->closed??0),
                    'rejected'=>(int)($row->rejected??0),
                    'cancelled'=>(int)($row->cancelled??0),
                    'death'=>(int)($row->death??0),
                ];
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Death nominee allocation
    |--------------------------------------------------------------------------
    */

    protected function buildDeathNomineeAllocations(
        MemberExit $exit
    ): void{
        $nominees=MemberNominee::query()
            ->select([
                'id',
                'allocation_percentage',
            ])
            ->where('member_id',$exit->member_id)
            ->where('is_active',true)
            ->where('verification_status','verified')
            ->orderBy('priority')
            ->orderBy('id')
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
            (float)$nominees->sum('allocation_percentage'),
            2
        );

        if(abs($totalPercentage-100)>0.009){
            throw ValidationException::withMessages([
                'nominee'=>[
                    'Verified active nominee allocation must total exactly 100% before death settlement approval.'
                ],
            ]);
        }

        DB::table('member_exit_nominee_allocations')
            ->where('member_exit_id',$exit->id)
            ->delete();

        $refund=round(
            (float)$exit->share_refund,
            2
        );

        $allocated=0.0;
        $lastId=(int)$nominees->last()->id;
        $now=now();
        $rows=[];

        foreach($nominees as $nominee){
            $nomineeId=(int)$nominee->id;

            if($nomineeId===$lastId){
                $amount=round(
                    $refund-$allocated,
                    2
                );
            }else{
                $amount=round(
                    $refund*
                    ((float)$nominee->allocation_percentage/100),
                    2
                );

                $allocated+=$amount;
            }

            $rows[]=[
                'member_exit_id'=>$exit->id,
                'member_nominee_id'=>$nomineeId,
                'allocation_percentage'=>$nominee->allocation_percentage,
                'amount'=>$amount,
                'created_at'=>$now,
                'updated_at'=>$now,
            ];
        }

        if(!empty($rows)){
            DB::table('member_exit_nominee_allocations')
                ->insert($rows);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Current blockers
    |--------------------------------------------------------------------------
    | Multiple COUNT round-trips -> one database round-trip.
    |--------------------------------------------------------------------------
    */

    protected function currentBlockerSummary(
        int $memberId,
        string $exitType
    ): array{
        $row=DB::selectOne("
            SELECT
                (
                    SELECT COUNT(*)
                    FROM subscription_dues sd
                    INNER JOIN member_subscriptions ms
                        ON ms.id=sd.member_subscription_id
                    WHERE ms.member_id=?
                    AND sd.status NOT IN ('paid','waived')
                    AND sd.amount>sd.paid_amount
                ) AS subscriptions,

                (
                    SELECT COUNT(*)
                    FROM member_charges mc
                    WHERE mc.member_id=?
                    AND mc.status NOT IN ('paid','waived','cancelled')
                    AND mc.amount>mc.paid_amount
                ) AS charges,

                (
                    SELECT COUNT(*)
                    FROM loans l
                    WHERE l.member_id=?
                    AND (
                        l.status='approved'
                        OR (
                            l.status IN ('active','overdue','defaulted')
                            AND l.total_payable>
                                COALESCE((
                                    SELECT SUM(lr.total_amount)
                                    FROM loan_repayments lr
                                    WHERE lr.loan_id=l.id
                                ),0)
                        )
                    )
                ) AS loans,

                (
                    SELECT COUNT(*)
                    FROM member_shares ms2
                    WHERE ms2.member_id=?
                    AND ms2.status='pending'
                ) AS pending_shares,

                (
                    SELECT COUNT(*)
                    FROM committee_members cm
                    WHERE cm.member_id=?
                    AND cm.status='active'
                ) AS committee
        ",[
            $memberId,
            $memberId,
            $memberId,
            $memberId,
            $memberId,
        ]);

        $subscriptions=(int)($row->subscriptions??0);
        $charges=(int)($row->charges??0);
        $loans=(int)($row->loans??0);
        $pendingShares=(int)($row->pending_shares??0);

        $committee=$exitType==='death'
            ?0
            :(int)($row->committee??0);

        return[
            'subscriptions'=>$subscriptions,
            'charges'=>$charges,
            'loans'=>$loans,
            'pending_shares'=>$pendingShares,
            'committee'=>$committee,
            'count'=>
                $subscriptions+
                $charges+
                $loans+
                $pendingShares+
                $committee,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Assessment item builder
    |--------------------------------------------------------------------------
    | Creates array data only. Actual INSERT is done in bulk.
    |--------------------------------------------------------------------------
    */

    protected function itemData(
        int $exitId,
        string $category,
        string $direction,
        ?string $referenceType,
        ?int $referenceId,
        string $description,
        float $amount,
        bool $blocking
    ): array{
        $now=now();

        return[
            'member_exit_id'=>$exitId,
            'category'=>$category,
            'direction'=>$direction,
            'reference_type'=>$referenceType,
            'reference_id'=>$referenceId,
            'description'=>$description,
            'amount'=>round($amount,2),
            'is_blocking'=>$blocking,
            'status'=>$blocking?'pending':'cleared',
            'created_at'=>$now,
            'updated_at'=>$now,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Cash/Bank leaf account
    |--------------------------------------------------------------------------
    */

    protected function cashBankAccount(
        int $accountId
    ): Account{
        $account=Account::query()
            ->select([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.sub_type',
            ])
            ->leftJoin(
                'accounts as child',
                'child.parent_id',
                '=',
                'accounts.id'
            )
            ->where('accounts.id',$accountId)
            ->where('accounts.is_active',true)
            ->where('accounts.type','asset')
            ->whereIn(
                'accounts.sub_type',
                ['cash','bank']
            )
            ->whereNull('child.id')
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

    /*
    |--------------------------------------------------------------------------
    | Row locking
    |--------------------------------------------------------------------------
    */

    protected function lockExit(
        MemberExit $exit
    ): MemberExit{
        return MemberExit::query()
            ->whereKey($exit->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Lightweight mutation response
    |--------------------------------------------------------------------------
    | Do not load items, nominees, transaction entries, etc.
    |--------------------------------------------------------------------------
    */

    public function freshExit(
        MemberExit $exit
    ): MemberExit{
        return MemberExit::query()
            ->with([
                'member'=>fn($q)=>$q
                    ->select([
                        'id',
                        'user_id',
                        'member_code',
                        'status',
                    ])
            ])
            ->findOrFail($exit->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Full detail response
    |--------------------------------------------------------------------------
    | Use only for show/detail page.
    |--------------------------------------------------------------------------
    */

    public function details(
        MemberExit $exit
    ): MemberExit{
        return MemberExit::query()
            ->with([
                'member'=>fn($q)=>$q
                    ->select([
                        'id',
                        'user_id',
                        'member_code',
                        'status',
                    ])
                    ->with(
                        'user:id,name,email,is_active'
                    ),

                'initiator:id,name',
                'reviewer:id,name',
                'approver:id,name',

                'payoutAccount:id,code,name,sub_type',

                'settlementTransaction'=>fn($q)=>$q
                    ->select([
                        'id',
                        'transaction_no',
                        'transaction_date',
                        'type',
                        'status',
                        'description',
                    ])
                    ->with([
                        'entries'=>fn($q)=>$q
                            ->select([
                                'id',
                                'transaction_id',
                                'account_id',
                                'debit',
                                'credit',
                                'description',
                            ])
                            ->with(
                                'account:id,code,name'
                            )
                    ]),

                'items'=>fn($q)=>$q
                    ->select([
                        'id',
                        'member_exit_id',
                        'category',
                        'direction',
                        'reference_type',
                        'reference_id',
                        'description',
                        'amount',
                        'is_blocking',
                        'status',
                    ])
                    ->orderByDesc('is_blocking')
                    ->orderBy('category')
                    ->orderBy('id'),

                'nomineeAllocations'=>fn($q)=>$q
                    ->select([
                        'id',
                        'member_exit_id',
                        'member_nominee_id',
                        'allocation_percentage',
                        'amount',
                    ])
                    ->with([
                        'nominee'=>fn($q)=>$q
                            ->select([
                                'id',
                                'member_id',
                                'name',
                                'relationship',
                                'phone',
                                'allocation_percentage',
                                'priority',
                            ])
                    ])
            ])
            ->findOrFail($exit->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Exit number
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    | Direct member -> user_id lookup avoids loading Member + User models.
    |--------------------------------------------------------------------------
    */

    protected function notify(
        MemberExit $exit,
        string $title,
        string $message,
        string $type,
        int $senderId
    ): void{
        $userId=DB::table('members')
            ->where('id',$exit->member_id)
            ->value('user_id');

        if(!$userId){
            return;
        }

        $userId=(int)$userId;

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
                'action_url'=>route('member.exit'),
                'sent_by'=>$senderId,
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cache invalidation
    |--------------------------------------------------------------------------
    */

    protected function forgetCaches(
        int $memberId
    ): void{
        DB::afterCommit(function()use($memberId){
            Cache::forget('member-exits:statistics');
            Cache::forget("member-exits:member:{$memberId}");

            $this->financeDashboard->forgetCache();
            $this->memberDashboard->forgetFinancialCache();
        });
    }

    protected function nullable(
        mixed $value
    ): ?string{
        if($value===null){
            return null;
        }

        $value=trim((string)$value);

        return $value===''?null:$value;
    }
}