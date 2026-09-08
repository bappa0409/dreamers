<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected NumberSequenceService $numberSequenceService,
        protected NotificationService $notificationService,
        protected FinanceDashboardService $financeDashboardService,
        protected MemberDashboardService $memberDashboardService
    ){}

    public function createRequest(array $data,int $userId): Loan
    {
        return DB::transaction(function()use($data,$userId){
            if(!filter_var(setting('loan_enabled',true),FILTER_VALIDATE_BOOLEAN)){
                throw ValidationException::withMessages([
                    'loan'=>['Loan facility is currently disabled.']
                ]);
            }

            $member=Member::query()
                ->whereKey($data['member_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member_id'=>['Only active members can request a loan.']
                ]);
            }

            $this->ensureNoOutstandingLoan($member);

            $amount=app_round((float)$data['requested_amount'],2);

            $this->validateMaximumAmount($amount);

            $loan=Loan::create([
                'loan_no'=>$this->numberSequenceService->next(
                    'loan',
                    'LN-'.now()->format('Y').'-',
                    6
                ),
                'member_id'=>$member->id,
                'requested_amount'=>$amount,
                'request_date'=>$data['request_date']??now()->toDateString(),
                'purpose'=>isset($data['purpose'])
                    ?trim($data['purpose'])
                    :null,
                'notes'=>isset($data['notes'])
                    ?trim($data['notes'])
                    :null,
                'status'=>'pending',
                'created_by'=>$userId
            ]);

            $this->forgetCachesAfterCommit();

            return $this->freshLoan($loan);
        });
    }

    public function updateRequest(Loan $loan,array $data): Loan
    {
        return DB::transaction(function()use($loan,$data){
            $loan=Loan::query()
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($loan->status!=='pending'){
                throw ValidationException::withMessages([
                    'loan'=>['Only pending loan requests can be edited.']
                ]);
            }

            if(array_key_exists('requested_amount',$data)){
                $amount=app_round((float)$data['requested_amount'],2);
                $this->validateMaximumAmount($amount);
                $loan->requested_amount=$amount;
            }

            foreach(['request_date','purpose','notes'] as $field){
                if(array_key_exists($field,$data)){
                    $loan->{$field}=is_string($data[$field])
                        ?trim($data[$field])
                        :$data[$field];
                }
            }

            $loan->save();
            $this->forgetCachesAfterCommit();

            return $this->freshLoan($loan);
        });
    }

    /**
     * Finalize a loan approval. Called by ApprovalService::executeApprovedAction()
     * once the generic multi-step approval workflow configured for module=Loan,
     * action=request has been fully signed off. $decisionData carries the
     * approved_amount/interest_rate/duration_months submitted by the final
     * approver (see ApprovalService::approve()'s $decisionData argument).
     */
    public function finalizeApproval(Loan $loan,array $decisionData,int $userId): Loan
    {
        return DB::transaction(function()use($loan,$decisionData,$userId){
            $loan=Loan::query()
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($loan->status!=='pending'){
                throw ValidationException::withMessages([
                    'loan'=>['Only pending loan requests can be approved.']
                ]);
            }

            $member=Member::query()
                ->whereKey($loan->member_id)
                ->lockForUpdate()
                ->firstOrFail();

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member'=>['Inactive member cannot receive loan approval.']
                ]);
            }

            $this->ensureNoOutstandingLoan($member,$loan->id);

            $approvedAmount=app_round(
                (float)($decisionData['approved_amount']??$loan->requested_amount),
                2
            );

            if($approvedAmount<=0){
                throw ValidationException::withMessages([
                    'approved_amount'=>['Approved amount must be greater than zero.']
                ]);
            }

            if($approvedAmount>(float)$loan->requested_amount){
                throw ValidationException::withMessages([
                    'approved_amount'=>[
                        'Approved amount cannot exceed requested amount.'
                    ]
                ]);
            }

            $this->validateMaximumAmount($approvedAmount);

            $interestRate=round(
                (float)($decisionData['interest_rate']
                    ??setting('loan_default_interest_rate',10)),
                4
            );

            if($interestRate<0||$interestRate>100){
                throw ValidationException::withMessages([
                    'interest_rate'=>['Interest rate must be between 0 and 100.']
                ]);
            }

            $duration=(int)($decisionData['duration_months']
                ??setting('loan_default_duration_months',12));

            if($duration<1||$duration>120){
                throw ValidationException::withMessages([
                    'duration_months'=>[
                        'Loan duration must be between 1 and 120 months.'
                    ]
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Simple flat interest
            |--------------------------------------------------------------------------
            |
            | Current business rule:
            | principal + fixed interest is paid once at maturity.
            |
            */
            $interestAmount=app_round(
                $approvedAmount*($interestRate/100),
                2
            );

            $totalPayable=app_round(
                $approvedAmount+$interestAmount,
                2
            );

            $loan->update([
                'approved_amount'=>$approvedAmount,
                'interest_rate'=>$interestRate,
                'interest_amount'=>$interestAmount,
                'total_payable'=>$totalPayable,
                'duration_months'=>$duration,
                'status'=>'approved',
                'approved_by'=>$userId,
                'approved_at'=>now(),
                'rejected_by'=>null,
                'rejected_at'=>null,
                'rejection_reason'=>null
            ]);

            $this->notifyMember(
                $loan,
                'Loan Approved',
                "Your loan {$loan->loan_no} has been approved for ".money($approvedAmount).'.',
                'success'
            );

            $this->forgetCachesAfterCommit();

            return $this->freshLoan($loan);
        });
    }

    /**
     * Finalize a loan rejection. Called by ApprovalService::executeRejectedAction()
     * once any step in the module=Loan, action=request workflow rejects the request.
     */
    public function finalizeRejection(
        Loan $loan,
        string $reason,
        int $userId
    ): Loan{
        return DB::transaction(function()use(
            $loan,
            $reason,
            $userId
        ){
            $loan=Loan::query()
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($loan->status!=='pending'){
                throw ValidationException::withMessages([
                    'loan'=>['Only pending loan requests can be rejected.']
                ]);
            }

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'rejection_reason'=>['Rejection reason is required.']
                ]);
            }

            $loan->update([
                'status'=>'rejected',
                'rejected_by'=>$userId,
                'rejected_at'=>now(),
                'rejection_reason'=>$reason
            ]);

            $this->notifyMember(
                $loan,
                'Loan Request Rejected',
                "Your loan request {$loan->loan_no} was rejected.",
                'warning'
            );

            $this->forgetCachesAfterCommit();

            return $this->freshLoan($loan);
        });
    }

    public function cancel(Loan $loan,int $userId): Loan
    {
        return DB::transaction(function()use($loan,$userId){
            $loan=Loan::query()
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(!in_array($loan->status,['pending','approved'],true)){
                throw ValidationException::withMessages([
                    'loan'=>[
                        'Only pending or approved loans can be cancelled before disbursement.'
                    ]
                ]);
            }

            if($loan->finance_transaction_id){
                throw ValidationException::withMessages([
                    'loan'=>[
                        'Disbursed loan cannot be cancelled directly.'
                    ]
                ]);
            }

            $loan->update([
                'status'=>'cancelled',
                'notes'=>trim(
                    ($loan->notes?($loan->notes."\n"):'').
                    'Cancelled by user #'.$userId.' on '.now()
                )
            ]);

            $this->forgetCachesAfterCommit();

            return $this->freshLoan($loan);
        });
    }

    public function disburse(
        Loan $loan,
        array $data,
        int $userId
    ): Loan{
        return DB::transaction(function()use(
            $loan,
            $data,
            $userId
        ){
            $loan=Loan::query()
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($loan->status!=='approved'){
                throw ValidationException::withMessages([
                    'loan'=>['Only approved loans can be disbursed.']
                ]);
            }

            if($loan->finance_transaction_id){
                throw ValidationException::withMessages([
                    'loan'=>['Loan has already been disbursed.']
                ]);
            }

            $member=Member::query()
                ->whereKey($loan->member_id)
                ->lockForUpdate()
                ->firstOrFail();

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member'=>[
                        'Only an active member can receive loan disbursement.'
                    ]
                ]);
            }

            $paymentAccount=$this->cashBankAccount(
                (int)$data['disbursement_account_id'],
                'disbursement_account_id'
            );

            $receivableAccount=$this->accountingService
                ->account('member_loan_receivable');

            $disbursementDate=$data['disbursement_date']
                ??now()->toDateString();

            if(
                $loan->request_date&&
                $disbursementDate<$loan->request_date->toDateString()
            ){
                throw ValidationException::withMessages([
                    'disbursement_date'=>[
                        'Disbursement date cannot be before the loan request date.'
                    ]
                ]);
            }

            $maturityDate=Carbon::parse($disbursementDate)
                ->addMonthsNoOverflow((int)$loan->duration_months)
                ->toDateString();

            $amount=app_round((float)$loan->approved_amount,2);

            /*
            |--------------------------------------------------------------------------
            | Dr Member Loan Receivable
            | Cr Cash / Bank
            |--------------------------------------------------------------------------
            */
            $transaction=$this->accountingService->post([
                'idempotency_key'=>"loan:disbursement:{$loan->id}",
                'transaction_date'=>$disbursementDate,
                'type'=>'loan_disbursement',
                'source_module'=>'loan',
                'source_id'=>$loan->id,
                'reference_type'=>Loan::class,
                'reference_id'=>$loan->id,
                'description'=>"Loan disbursement {$loan->loan_no}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$receivableAccount->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>"Loan receivable {$loan->loan_no}"
                    ],
                    [
                        'account_id'=>$paymentAccount->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>"Loan disbursement {$loan->loan_no}"
                    ]
                ]
            ]);

            $loan->update([
                'disbursement_account_id'=>$paymentAccount->id,
                'disbursement_date'=>$disbursementDate,
                'maturity_date'=>$maturityDate,
                'finance_transaction_id'=>$transaction->id,
                'status'=>'active'
            ]);

            $this->notifyMember(
                $loan,
                'Loan Disbursed',
                "Your loan {$loan->loan_no} of ".money($amount).
                " has been disbursed. Maturity date: ".app_date($maturityDate).'.',
                'success'
            );

            $this->forgetCachesAfterCommit();

            return $this->freshLoan($loan);
        });
    }

    public function repay(
        Loan $loan,
        array $data,
        int $userId
    ): LoanRepayment{
        return DB::transaction(function()use(
            $loan,
            $data,
            $userId
        ){
            $loan=Loan::query()
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(!in_array($loan->status,['active','overdue','defaulted'],true)){
                throw ValidationException::withMessages([
                    'loan'=>[
                        'Only active, overdue or defaulted loan can be repaid.'
                    ]
                ]);
            }

            if($loan->repayments()->exists()){
                throw ValidationException::withMessages([
                    'loan'=>[
                        'This loan already has a repayment. Current policy allows one final repayment only.'
                    ]
                ]);
            }

            $receiveAccount=$this->cashBankAccount(
                (int)$data['receive_account_id'],
                'receive_account_id'
            );

            $principal=app_round((float)$loan->approved_amount,2);
            $interest=app_round((float)$loan->interest_amount,2);
            $penalty=0.00;

            $expectedTotal=app_round(
                $principal+$interest+$penalty,
                2
            );

            $receivedTotal=app_round(
                (float)$data['total_amount'],
                2
            );

            if(abs($receivedTotal-$expectedTotal)>0.009){
                throw ValidationException::withMessages([
                    'total_amount'=>[
                        'Current loan policy requires full principal + interest payment of '.
                        money($expectedTotal).'.'
                    ]
                ]);
            }

            $repaymentDate=$data['repayment_date']
                ??now()->toDateString();

            if(
                $loan->disbursement_date&&
                $repaymentDate<$loan->disbursement_date->toDateString()
            ){
                throw ValidationException::withMessages([
                    'repayment_date'=>[
                        'Repayment date cannot be before disbursement date.'
                    ]
                ]);
            }

            $receivableAccount=$this->accountingService
                ->account('member_loan_receivable');

            $interestAccount=$this->accountingService
                ->account('loan_interest_income');

            /*
            |--------------------------------------------------------------------------
            | Dr Cash / Bank = principal + interest
            | Cr Loan Receivable = principal
            | Cr Loan Interest Income = interest
            |--------------------------------------------------------------------------
            */
            $entries=[
                [
                    'account_id'=>$receiveAccount->id,
                    'debit'=>$expectedTotal,
                    'credit'=>0,
                    'description'=>"Loan repayment {$loan->loan_no}"
                ],
                [
                    'account_id'=>$receivableAccount->id,
                    'debit'=>0,
                    'credit'=>$principal,
                    'description'=>"Principal repayment {$loan->loan_no}"
                ]
            ];

            if($interest>0){
                $entries[]=[
                    'account_id'=>$interestAccount->id,
                    'debit'=>0,
                    'credit'=>$interest,
                    'description'=>"Loan interest {$loan->loan_no}"
                ];
            }

            $transaction=$this->accountingService->post([
                'idempotency_key'=>"loan:repayment:{$loan->id}",
                'transaction_date'=>$repaymentDate,
                'type'=>'loan_repayment',
                'source_module'=>'loan',
                'source_id'=>$loan->id,
                'reference_type'=>Loan::class,
                'reference_id'=>$loan->id,
                'description'=>"Loan repayment {$loan->loan_no}",
                'user_id'=>$userId,
                'entries'=>$entries
            ]);

            $repayment=LoanRepayment::create([
                'loan_id'=>$loan->id,
                'receive_account_id'=>$receiveAccount->id,
                'principal_amount'=>$principal,
                'interest_amount'=>$interest,
                'penalty_amount'=>$penalty,
                'total_amount'=>$expectedTotal,
                'repayment_date'=>$repaymentDate,
                'notes'=>isset($data['notes'])
                    ?trim($data['notes'])
                    :null,
                'finance_transaction_id'=>$transaction->id,
                'received_by'=>$userId
            ]);

            $loan->update([
                'status'=>'repaid'
            ]);

            $this->notifyMember(
                $loan,
                'Loan Repaid',
                "Your loan {$loan->loan_no} has been fully repaid and closed.",
                'success'
            );

            $this->forgetCachesAfterCommit();

            return $repayment->load([
                'loan.member.user',
                'receiveAccount',
                'financeTransaction.entries.account',
                'receiver:id,name'
            ]);
        });
    }

    public function markOverdueLoans(): int
    {
        $count=0;

        Loan::query()
            ->where('status','active')
            ->whereDate('maturity_date','<',now()->toDateString())
            ->orderBy('id')
            ->chunkById(100,function($loans)use(&$count){
                foreach($loans as $loan){
                    $loan->update(['status'=>'overdue']);
                    $count++;
                }
            });

        if($count>0){
            $this->forgetCachesAfterCommit();
        }

        return $count;
    }

    public function markDefaulted(Loan $loan): Loan
    {
        return DB::transaction(function()use($loan){
            $loan=Loan::query()
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($loan->status!=='overdue'){
                throw ValidationException::withMessages([
                    'loan'=>['Only overdue loans can be marked as defaulted.']
                ]);
            }

            $loan->update(['status'=>'defaulted']);

            $this->forgetCachesAfterCommit();

            return $this->freshLoan($loan);
        });
    }

    public function statistics(): array
    {
        return Cache::remember(
            'loan:statistics',
            now()->addMinutes(5),
            function(){
                return[
                    'total'=>Loan::count(),
                    'pending'=>Loan::where('status','pending')->count(),
                    'approved'=>Loan::where('status','approved')->count(),
                    'active'=>Loan::whereIn('status',['active','overdue','defaulted'])->count(),
                    'overdue'=>Loan::whereIn('status',['overdue','defaulted'])->count(),
                    'repaid'=>Loan::where('status','repaid')->count(),
                    'outstanding_principal'=>$this
                        ->outstandingPrincipal()
                ];
            }
        );
    }

    protected function outstandingPrincipal(): float
    {
        $activeLoanIds=Loan::query()
            ->whereIn(
                'status',
                ['active','overdue','defaulted']
            )
            ->select('id');

        $approved=(float)Loan::query()
            ->whereIn(
                'status',
                ['active','overdue','defaulted']
            )
            ->sum('approved_amount');

        $principalRepaid=(float)LoanRepayment::query()
            ->whereIn('loan_id',$activeLoanIds)
            ->sum('principal_amount');

        return max(
            app_round(
                $approved-$principalRepaid,
                2
            ),
            0
        );
    }

    protected function ensureNoOutstandingLoan(
        Member $member,
        ?int $ignoreLoanId=null
    ): void{
        if(filter_var(
            setting('loan_allow_multiple_active',false),
            FILTER_VALIDATE_BOOLEAN
        )){
            return;
        }

        $exists=Loan::query()
            ->where('member_id',$member->id)
            ->whereIn('status',[
                'approved',
                'active',
                'overdue',
                'defaulted'
            ])
            ->when(
                $ignoreLoanId,
                fn($q)=>$q->whereKeyNot($ignoreLoanId)
            )
            ->exists();

        if($exists){
            throw ValidationException::withMessages([
                'member_id'=>[
                    'This member already has an outstanding loan.'
                ]
            ]);
        }
    }

    protected function validateMaximumAmount(float $amount): void
    {
        if($amount<=0){
            throw ValidationException::withMessages([
                'requested_amount'=>[
                    'Loan amount must be greater than zero.'
                ]
            ]);
        }

        $maximum=(float)setting('loan_max_amount',0);

        if($maximum>0&&$amount>$maximum){
            throw ValidationException::withMessages([
                'requested_amount'=>[
                    'Maximum allowed loan amount is '.money($maximum).'.'
                ]
            ]);
        }
    }

    protected function cashBankAccount(
        int $accountId,
        string $field
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->where('type','asset')
            ->where('is_active',true)
            ->whereIn('sub_type',['cash','bank'])
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                $field=>[
                    'A valid active Cash or Bank posting account is required.'
                ]
            ]);
        }

        return $account;
    }

    protected function notifyMember(
        Loan $loan,
        string $title,
        string $message,
        string $type='info'
    ): void{
        $loan->loadMissing('member.user');

        if(!$loan->member?->user_id){
            return;
        }

        DB::afterCommit(function()use(
            $loan,
            $title,
            $message,
            $type
        ){
            $this->notificationService->sendSystem([
                'title'=>$title,
                'message'=>$message,
                'type'=>$type,
                'audience_type'=>'users',
                'user_ids'=>[$loan->member->user_id],
                'action_url'=>route('member.loans'),
                'sent_by'=>auth()->id()
            ]);
        });
    }

    public function freshLoan(Loan $loan): Loan
    {
        return $loan->fresh([
            'member.user:id,name,email',
            'approver:id,name',
            'rejector:id,name',
            'creator:id,name',
            'disbursementAccount:id,code,name,sub_type',
            'financeTransaction.entries.account',
            'repayments'=>fn($q)=>$q
                ->with([
                    'receiveAccount:id,code,name',
                    'receiver:id,name',
                    'financeTransaction.entries.account'
                ])
                ->latest('repayment_date')
        ]);
    }

    protected function forgetCachesAfterCommit(): void
    {
        DB::afterCommit(function(){
            Cache::forget('loan:statistics');
            $this->financeDashboardService->forgetCache();
            $this->memberDashboardService->forgetFinancialCache();
        });
    }
}