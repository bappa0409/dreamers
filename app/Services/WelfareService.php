<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\WelfareDocument;
use App\Models\WelfareFund;
use App\Models\WelfareFundAllocation;
use App\Models\WelfareRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WelfareService
{
    public function __construct(
        protected AccountingService $accounting,
        protected NumberSequenceService $numbers,
        protected NotificationService $notifications,
        protected FinanceDashboardService $financeDashboard,
        protected MemberDashboardService $memberDashboard
    ){}

    public function createFund(array $data,int $userId): WelfareFund
    {
        $expenseAccount=Account::query()
            ->whereKey($data['expense_account_id'])
            ->where('is_active',true)
            ->where('type','expense')
            ->whereDoesntHave('children')
            ->first();

        if(!$expenseAccount){
            throw ValidationException::withMessages([
                'expense_account_id'=>[
                    'A valid active expense posting account is required.'
                ]
            ]);
        }

        if(
            !empty($data['end_date'])&&
            !empty($data['start_date'])&&
            $data['end_date']<$data['start_date']
        ){
            throw ValidationException::withMessages([
                'end_date'=>[
                    'End date cannot be before start date.'
                ]
            ]);
        }

        $fund=WelfareFund::create([
            'code'=>strtoupper(trim($data['code'])),
            'name'=>trim($data['name']),
            'description'=>$this->nullable($data['description']??null),
            'expense_account_id'=>$expenseAccount->id,
            'start_date'=>$data['start_date']??null,
            'end_date'=>$data['end_date']??null,
            'is_active'=>$data['is_active']??true,
            'created_by'=>$userId
        ]);

        $this->forgetCaches();

        return $fund->load('expenseAccount');
    }

    public function addAllocation(
        WelfareFund $fund,
        array $data,
        int $userId
    ): WelfareFundAllocation{
        return DB::transaction(function()use(
            $fund,$data,$userId
        ){
            $fund=WelfareFund::query()
                ->whereKey($fund->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(!$fund->is_active){
                throw ValidationException::withMessages([
                    'fund'=>['Inactive fund cannot receive allocations.']
                ]);
            }

            $amount=app_round((float)$data['amount'],2);

            if($amount<=0){
                throw ValidationException::withMessages([
                    'amount'=>['Allocation must be greater than zero.']
                ]);
            }

            $transactionId=$data['source_transaction_id']??null;

            if($transactionId){
                $valid=Transaction::query()
                    ->whereKey($transactionId)
                    ->where('status','posted')
                    ->exists();

                if(!$valid){
                    throw ValidationException::withMessages([
                        'source_transaction_id'=>[
                            'Source finance transaction must be posted.'
                        ]
                    ]);
                }
            }

            $allocation=WelfareFundAllocation::create([
                'welfare_fund_id'=>$fund->id,
                'amount'=>$amount,
                'allocation_date'=>$data['allocation_date'],
                'source_type'=>$data['source_type'],
                'source_reference'=>$this->nullable(
                    $data['source_reference']??null
                ),
                'description'=>$this->nullable(
                    $data['description']??null
                ),
                'source_transaction_id'=>$transactionId,
                'created_by'=>$userId
            ]);

            $this->forgetCaches();

            return $allocation->load([
                'fund:id,code,name',
                'sourceTransaction:id,transaction_no',
                'creator:id,name'
            ]);
        });
    }

    public function createRequest(
        array $data,
        int $userId
    ): WelfareRequest{
        return DB::transaction(function()use($data,$userId){
            $member=Member::query()
                ->whereKey($data['member_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member_id'=>[
                        'Welfare assistance can only be requested for an active member.'
                    ]
                ]);
            }

            $fund=WelfareFund::query()
                ->whereKey($data['welfare_fund_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if(!$this->fundAvailable($fund)){
                throw ValidationException::withMessages([
                    'welfare_fund_id'=>[
                        'Selected welfare fund is not currently active.'
                    ]
                ]);
            }

            $amount=app_round(
                (float)$data['requested_amount'],
                2
            );

            if($amount<=0){
                throw ValidationException::withMessages([
                    'requested_amount'=>[
                        'Requested amount must be greater than zero.'
                    ]
                ]);
            }

            $request=WelfareRequest::create([
                'request_no'=>$this->numbers->next(
                    'welfare-request',
                    'WF-',
                    6
                ),
                'welfare_fund_id'=>$fund->id,
                'member_id'=>$data['member_id'],
                'assistance_type'=>$data['assistance_type'],
                'requested_amount'=>$amount,
                'reason'=>trim($data['reason']),
                'notes'=>$this->nullable($data['notes']??null),
                'request_date'=>$data['request_date']??now()->toDateString(),
                'status'=>'submitted',
                'created_by'=>$userId
            ]);

            $this->history(
                $request,
                null,
                'submitted',
                'Welfare assistance request submitted.',
                $userId
            );

            $this->forgetCaches();

            return $this->freshRequest($request);
        });
    }

    public function review(
        WelfareRequest $request,
        ?string $note,
        int $userId
    ): WelfareRequest{
        return DB::transaction(function()use(
            $request,$note,$userId
        ){
            $request=$this->lockRequest($request);

            if($request->status!=='submitted'){
                throw ValidationException::withMessages([
                    'request'=>[
                        'Only submitted requests can enter review.'
                    ]
                ]);
            }

            $old=$request->status;

            $request->update([
                'status'=>'under_review',
                'reviewed_by'=>$userId,
                'reviewed_at'=>now(),
                'review_note'=>$this->nullable($note)
            ]);

            $this->history(
                $request,
                $old,
                'under_review',
                $note,
                $userId
            );

            return $this->freshRequest($request);
        });
    }

    /**
     * Finalize a welfare-request approval. Called by
     * ApprovalService::executeApprovedAction() once the module=Welfare,
     * action=request workflow has been fully signed off. $decisionData
     * carries the approved_amount submitted by the final approver.
     */
    public function finalizeApproval(
        WelfareRequest $request,
        array $decisionData,
        int $userId
    ): WelfareRequest{
        return DB::transaction(function()use(
            $request,
            $decisionData,
            $userId
        ){
            $request=$this->lockRequest($request);

            if(!in_array(
                $request->status,
                ['submitted','under_review'],
                true
            )){
                throw ValidationException::withMessages([
                    'request'=>[
                        'This request cannot be approved.'
                    ]
                ]);
            }

            $fund=WelfareFund::query()
                ->whereKey($request->welfare_fund_id)
                ->lockForUpdate()
                ->firstOrFail();

            if(!$this->fundAvailable($fund)){
                throw ValidationException::withMessages([
                    'fund'=>['Welfare fund is inactive.']
                ]);
            }

            $approvedAmount=app_round(
                (float)($decisionData['approved_amount']??$request->requested_amount),
                2
            );

            if(
                $approvedAmount<=0||
                $approvedAmount>(float)$request->requested_amount
            ){
                throw ValidationException::withMessages([
                    'approved_amount'=>[
                        'Approved amount must be greater than zero and cannot exceed requested amount.'
                    ]
                ]);
            }

            $available=$this->availableForApproval(
                $fund->id,
                $request->id
            );

            if($approvedAmount>$available){
                throw ValidationException::withMessages([
                    'approved_amount'=>[
                        'Insufficient welfare fund allocation. Available amount is '.
                        money($available).'.'
                    ]
                ]);
            }

            $old=$request->status;

            $request->update([
                'approved_amount'=>$approvedAmount,
                'status'=>'approved',
                'approved_by'=>$userId,
                'approved_at'=>now(),
                'rejected_by'=>null,
                'rejected_at'=>null,
                'rejection_reason'=>null
            ]);

            $this->history(
                $request,
                $old,
                'approved',
                'Assistance request approved.',
                $userId
            );

            $this->notifyMember(
                $request,
                'Welfare Assistance Approved',
                "{$request->request_no} has been approved for ".
                money($approvedAmount).'.',
                'success',
                $userId
            );

            $this->forgetCaches();

            return $this->freshRequest($request);
        });
    }

    /**
     * Finalize a welfare-request rejection. Called by
     * ApprovalService::executeRejectedAction().
     */
    public function finalizeRejection(
        WelfareRequest $request,
        string $reason,
        int $userId
    ): WelfareRequest{
        return DB::transaction(function()use(
            $request,$reason,$userId
        ){
            $request=$this->lockRequest($request);

            if(!in_array(
                $request->status,
                ['submitted','under_review'],
                true
            )){
                throw ValidationException::withMessages([
                    'request'=>[
                        'This request cannot be rejected.'
                    ]
                ]);
            }

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'rejection_reason'=>[
                        'Rejection reason is required.'
                    ]
                ]);
            }

            $old=$request->status;

            $request->update([
                'status'=>'rejected',
                'rejected_by'=>$userId,
                'rejected_at'=>now(),
                'rejection_reason'=>$reason
            ]);

            $this->history(
                $request,
                $old,
                'rejected',
                $reason,
                $userId
            );

            $this->notifyMember(
                $request,
                'Welfare Assistance Rejected',
                "{$request->request_no} has been rejected.",
                'warning',
                $userId
            );

            $this->forgetCaches();

            return $this->freshRequest($request);
        });
    }

    public function cancel(
        WelfareRequest $request,
        int $userId
    ): WelfareRequest{
        return DB::transaction(function()use(
            $request,$userId
        ){
            $request=$this->lockRequest($request);

            if(!in_array(
                $request->status,
                ['submitted','under_review','approved'],
                true
            )){
                throw ValidationException::withMessages([
                    'request'=>[
                        'Completed or rejected assistance cannot be cancelled.'
                    ]
                ]);
            }

            if($request->finance_transaction_id){
                throw ValidationException::withMessages([
                    'request'=>[
                        'Disbursed assistance must be reversed instead of cancelled.'
                    ]
                ]);
            }

            $old=$request->status;

            $request->update([
                'status'=>'cancelled'
            ]);

            $this->history(
                $request,
                $old,
                'cancelled',
                'Request cancelled.',
                $userId
            );

            $this->forgetCaches();

            return $this->freshRequest($request);
        });
    }

    public function disburse(
        WelfareRequest $request,
        array $data,
        int $userId
    ): WelfareRequest{
        return DB::transaction(function()use(
            $request,$data,$userId
        ){
            $request=$this->lockRequest($request);

            if($request->status!=='approved'){
                throw ValidationException::withMessages([
                    'request'=>[
                        'Only approved welfare requests can be disbursed.'
                    ]
                ]);
            }

            if($request->finance_transaction_id){
                throw ValidationException::withMessages([
                    'request'=>[
                        'This request has already been disbursed.'
                    ]
                ]);
            }

            $requestDate=$request->request_date?->toDateString();

            if(
                $requestDate&&
                $data['disbursement_date']<$requestDate
            ){
                throw ValidationException::withMessages([
                    'disbursement_date'=>[
                        'Disbursement date cannot be before the welfare request date.'
                    ]
                ]);
            }

            $fund=WelfareFund::query()
                ->whereKey($request->welfare_fund_id)
                ->lockForUpdate()
                ->firstOrFail();

            $available=$this->availableIncludingReservation(
                $fund->id,
                $request->id
            );

            $amount=app_round(
                (float)$request->approved_amount,
                2
            );

            if($amount>$available){
                throw ValidationException::withMessages([
                    'fund'=>[
                        'Welfare fund no longer has sufficient available allocation.'
                    ]
                ]);
            }

            $paymentAccount=$this->cashBankAccount(
                (int)$data['payment_account_id']
            );

            $expenseAccount=Account::query()
                ->whereKey($fund->expense_account_id)
                ->where('is_active',true)
                ->where('type','expense')
                ->whereDoesntHave('children')
                ->first();

            if(!$expenseAccount){
                throw ValidationException::withMessages([
                    'fund'=>[
                        'Welfare fund expense account is invalid.'
                    ]
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Accounting
            |--------------------------------------------------------------------------
            |
            | Dr Welfare Assistance Expense
            | Cr Cash / Bank
            |
            */

            $transaction=$this->accounting->post([
                'idempotency_key'=>
                    "welfare:disbursement:{$request->id}",

                'transaction_date'=>$data['disbursement_date'],

                'type'=>'welfare_assistance',
                'source_module'=>'welfare',
                'source_id'=>$request->id,
                'reference_type'=>WelfareRequest::class,
                'reference_id'=>$request->id,

                'description'=>
                    "Welfare assistance {$request->request_no}",

                'user_id'=>$userId,

                'entries'=>[
                    [
                        'account_id'=>$expenseAccount->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>
                            "Welfare assistance {$request->request_no}"
                    ],
                    [
                        'account_id'=>$paymentAccount->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>
                            "Welfare payment {$request->request_no}"
                    ]
                ]
            ]);

            $old=$request->status;

            $request->update([
                'payment_account_id'=>$paymentAccount->id,
                'disbursement_date'=>$data['disbursement_date'],
                'finance_transaction_id'=>$transaction->id,
                'status'=>'completed',
                'completed_at'=>now()
            ]);

            $this->history(
                $request,
                $old,
                'completed',
                'Assistance disbursed and accounting posted.',
                $userId
            );

            $this->notifyMember(
                $request,
                'Welfare Assistance Paid',
                money($amount).
                " has been disbursed for {$request->request_no}.",
                'success',
                $userId
            );

            $this->forgetCaches();

            return $this->freshRequest($request);
        });
    }

    public function reverse(
        WelfareRequest $request,
        string $reason,
        int $userId
    ): WelfareRequest{
        return DB::transaction(function()use(
            $request,$reason,$userId
        ){
            $request=$this->lockRequest($request);

            if(
                $request->status!=='completed'||
                !$request->finance_transaction_id
            ){
                throw ValidationException::withMessages([
                    'request'=>[
                        'Only completed assistance can be reversed.'
                    ]
                ]);
            }

            if($request->reversal_transaction_id){
                throw ValidationException::withMessages([
                    'request'=>[
                        'This assistance has already been reversed.'
                    ]
                ]);
            }

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'reason'=>[
                        'Reversal reason is required.'
                    ]
                ]);
            }

            $request->loadMissing(
                'financeTransaction.entries'
            );

            $entries=$request
                ->financeTransaction
                ->entries
                ->map(fn($entry)=>[
                    'account_id'=>$entry->account_id,
                    'debit'=>(float)$entry->credit,
                    'credit'=>(float)$entry->debit,
                    'description'=>
                        "Reversal {$request->request_no}"
                ])
                ->all();

            $originalDate=$request->financeTransaction?->transaction_date?->toDateString()
                ??$request->disbursement_date?->toDateString()
                ??now()->toDateString();

            $reversalDate=max(
                now()->toDateString(),
                $originalDate
            );

            $reversal=$this->accounting->post([
                'idempotency_key'=>
                    "welfare:reversal:{$request->id}",

                'transaction_date'=>$reversalDate,
                'type'=>'welfare_assistance_reversal',
                'source_module'=>'welfare',
                'source_id'=>$request->id,
                'reference_type'=>WelfareRequest::class,
                'reference_id'=>$request->id,

                'description'=>
                    "Reversal of {$request->request_no}: {$reason}",

                'user_id'=>$userId,
                'entries'=>$entries
            ]);

            $old=$request->status;

            $request->update([
                'reversal_transaction_id'=>$reversal->id,
                'status'=>'reversed'
            ]);

            $this->history(
                $request,
                $old,
                'reversed',
                $reason,
                $userId
            );

            $this->forgetCaches();

            return $this->freshRequest($request);
        });
    }

    public function uploadDocument(
        WelfareRequest $request,
        UploadedFile $file,
        string $type,
        int $userId
    ): WelfareDocument{
        $filename=
            bin2hex(random_bytes(16)).
            '.'.
            strtolower($file->getClientOriginalExtension());

        $path=null;

        try{
            return DB::transaction(function()use(
                $request,$file,$type,$userId,$filename,&$path
            ){
                $request=$this->lockRequest($request);

                if(in_array(
                    $request->status,
                    ['rejected','completed','cancelled','reversed'],
                    true
                )){
                    throw ValidationException::withMessages([
                        'request'=>[
                            'Documents cannot be added to this welfare request.'
                        ]
                    ]);
                }

                $path=$file->storeAs(
                    "welfare/{$request->member_id}/{$request->id}",
                    $filename,
                    'local'
                );

                return WelfareDocument::create([
                    'welfare_request_id'=>$request->id,
                    'document_type'=>$type,
                    'file_path'=>$path,
                    'original_name'=>$file->getClientOriginalName(),
                    'mime_type'=>$file->getMimeType(),
                    'file_size'=>$file->getSize(),
                    'uploaded_by'=>$userId
                ]);
            });
        }catch(\Throwable $e){
            if($path){
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    public function removeDocument(
        WelfareDocument $document
    ): void{
        $document->delete();
    }

    public function fundSummary(WelfareFund $fund): array
    {
        $allocated=app_round(
            (float)$fund->allocations()->sum('amount'),
            2
        );

        $committed=app_round(
            (float)$fund->requests()
                ->whereIn(
                    'status',
                    ['approved','completed']
                )
                ->sum('approved_amount'),
            2
        );

        $spent=app_round(
            (float)$fund->requests()
                ->where('status','completed')
                ->sum('approved_amount'),
            2
        );

        return[
            'allocated'=>$allocated,
            'committed'=>$committed,
            'spent'=>$spent,
            'available'=>max(
                0,
                app_round($allocated-$committed,2)
            )
        ];
    }

    public function statistics(): array
    {
        return Cache::remember(
            'welfare:statistics',
            now()->addMinutes(5),
            fn()=>[
                'funds'=>WelfareFund::where(
                    'is_active',
                    true
                )->count(),

                'submitted'=>WelfareRequest::whereIn(
                    'status',
                    ['submitted','under_review']
                )->count(),

                'approved'=>WelfareRequest::where(
                    'status',
                    'approved'
                )->count(),

                'completed'=>WelfareRequest::where(
                    'status',
                    'completed'
                )->count(),

                'total_disbursed'=>app_round(
                    (float)WelfareRequest::where(
                        'status',
                        'completed'
                    )->sum('approved_amount'),
                    2
                )
            ]
        );
    }

    protected function availableForApproval(
        int $fundId,
        ?int $ignoreRequestId=null
    ): float{
        $allocated=(float)WelfareFundAllocation::query()
            ->where('welfare_fund_id',$fundId)
            ->sum('amount');

        $committed=(float)WelfareRequest::query()
            ->where('welfare_fund_id',$fundId)
            ->whereIn('status',[
                'approved',
                'completed'
            ])
            ->when(
                $ignoreRequestId,
                fn($q)=>$q->whereKeyNot(
                    $ignoreRequestId
                )
            )
            ->sum('approved_amount');

        return max(
            0,
            app_round($allocated-$committed,2)
        );
    }

    protected function availableIncludingReservation(
        int $fundId,
        int $requestId
    ): float{
        $request=WelfareRequest::findOrFail(
            $requestId
        );

        return app_round(
            $this->availableForApproval(
                $fundId,
                $requestId
            )+
            (float)$request->approved_amount,
            2
        );
    }

    protected function fundAvailable(
        WelfareFund $fund
    ): bool{
        if(!$fund->is_active){
            return false;
        }

        $today=now()->toDateString();

        if(
            $fund->start_date&&
            $fund->start_date->toDateString()>$today
        ){
            return false;
        }

        if(
            $fund->end_date&&
            $fund->end_date->toDateString()<$today
        ){
            return false;
        }

        return true;
    }

    protected function cashBankAccount(
        int $id
    ): Account{
        $account=Account::query()
            ->whereKey($id)
            ->where('is_active',true)
            ->where('type','asset')
            ->whereIn(
                'sub_type',
                ['cash','bank']
            )
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'payment_account_id'=>[
                    'Valid active Cash or Bank account is required.'
                ]
            ]);
        }

        return $account;
    }

    protected function lockRequest(
        WelfareRequest $request
    ): WelfareRequest{
        return WelfareRequest::query()
            ->whereKey($request->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function history(
        WelfareRequest $request,
        ?string $from,
        string $to,
        ?string $note,
        int $userId
    ): void{
        $request->histories()->create([
            'from_status'=>$from,
            'to_status'=>$to,
            'note'=>$this->nullable($note),
            'changed_by'=>$userId
        ]);
    }

    protected function notifyMember(
        WelfareRequest $request,
        string $title,
        string $message,
        string $type,
        int $senderId
    ): void{
        $request->loadMissing(
            'member.user'
        );

        if(!$request->member?->user_id){
            return;
        }

        $userId=$request->member->user_id;

        DB::afterCommit(function()use(
            $title,
            $message,
            $type,
            $senderId,
            $userId
        ){
            $this->notifications->sendSystem([
                'title'=>$title,
                'message'=>$message,
                'type'=>$type,
                'audience_type'=>'users',
                'user_ids'=>[$userId],
                'action_url'=>route(
                    'member.welfare'
                ),
                'sent_by'=>$senderId
            ]);
        });
    }

    public function freshRequest(
        WelfareRequest $request
    ): WelfareRequest{
        return $request->fresh([
            'fund.expenseAccount',
            'member.user:id,name,email',
            'paymentAccount:id,code,name',
            'financeTransaction.entries.account',
            'reversalTransaction.entries.account',
            'documents.uploader:id,name',
            'histories.user:id,name'
        ]);
    }

    protected function forgetCaches(): void
    {
        DB::afterCommit(function(){
            Cache::forget(
                'welfare:statistics'
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

        return $value===''?null:$value;
    }
}