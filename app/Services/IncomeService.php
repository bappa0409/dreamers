<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Income;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class IncomeService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected NumberSequenceService $numberSequenceService
    ){}

    public function create(array $data,int $userId): Income
    {
        return DB::transaction(function()use($data,$userId){
            $incomeAccount=$this->incomeAccount(
                (int)$data['income_account_id']
            );

            $receiveAccount=$this->cashBankAccount(
                (int)$data['receive_account_id'],
                'receive_account_id'
            );

            $amount=round(
                (float)$data['amount'],
                2
            );

            if($amount<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Income amount must be greater than zero.'
                    ],
                ]);
            }

            $income=Income::create([
                'income_no'=>$this->generateNumber(),
                'member_id'=>$data['member_id']??null,
                'income_account_id'=>$incomeAccount->id,
                'receive_account_id'=>$receiveAccount->id,
                'amount'=>$amount,
                'income_date'=>$data['income_date'],
                'reference'=>$this->nullableString(
                    $data['reference']??null
                ),
                'description'=>$this->nullableString(
                    $data['description']??null
                ),
                'attachment'=>$data['attachment']??null,
                'created_by'=>$userId,
                'status'=>'pending_approval',
            ]);

            // No ledger entry yet: nothing is posted until the
            // Income.create approval request is approved — see
            // finalizeApproval().
            return $this->freshIncome($income);
        });
    }

    /**
     * Called by ApprovalService once the Income.create request is
     * approved. Posts the actual double-entry ledger transaction and
     * marks the income as posted.
     */
    public function finalizeApproval(
        Income $income,
        array $decisionData,
        int $approvedBy
    ): Income{
        return DB::transaction(function()use(
            $income,
            $approvedBy
        ){
            $income=Income::query()
                ->whereKey($income->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($income->status!=='pending_approval'){
                throw ValidationException::withMessages([
                    'income'=>[
                        'This income is not awaiting approval.'
                    ],
                ]);
            }

            $journal=$this->accountingService->post([
                'idempotency_key'=>"income:post:{$income->id}",
                'transaction_date'=>$income
                    ->income_date
                    ->toDateString(),
                'type'=>'income',
                'source_module'=>'income',
                'source_id'=>$income->id,
                'reference_type'=>Income::class,
                'reference_id'=>$income->id,
                'description'=>$income->description
                    ??"Income {$income->income_no}",
                'user_id'=>$approvedBy,
                'entries'=>[
                    [
                        'account_id'=>$income->receive_account_id,
                        'debit'=>$income->amount,
                        'credit'=>0,
                        'description'=>'Income received',
                    ],
                    [
                        'account_id'=>$income->income_account_id,
                        'debit'=>0,
                        'credit'=>$income->amount,
                        'description'=>'Income recognized',
                    ],
                ],
            ]);

            $income->update([
                'finance_transaction_id'=>$journal->id,
                'status'=>'posted',
            ]);

            return $this->freshIncome($income);
        });
    }

    /**
     * Called by ApprovalService when the Income.create request is
     * rejected. The income never gets a ledger entry.
     */
    public function finalizeRejection(
        Income $income,
        string $reason,
        ?int $rejectedBy
    ): Income{
        $income=Income::query()
            ->whereKey($income->id)
            ->lockForUpdate()
            ->firstOrFail();

        if($income->status==='pending_approval'){
            $income->update([
                'status'=>'rejected',
            ]);
        }

        return $this->freshIncome($income);
    }

    /**
     * Called by ApprovalService when the Income.create request is
     * cancelled/withdrawn before a decision is made.
     */
    public function finalizeCancellation(Income $income): Income
    {
        $income=Income::query()
            ->whereKey($income->id)
            ->lockForUpdate()
            ->firstOrFail();

        if($income->status==='pending_approval'){
            $income->update([
                'status'=>'cancelled',
            ]);
        }

        return $this->freshIncome($income);
    }

    public function update(
        Income $income,
        array $data
    ): Income{
        return DB::transaction(function()use(
            $income,
            $data
        ){
            $income=Income::query()
                ->whereKey($income->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($income->status==='cancelled'){
                throw ValidationException::withMessages([
                    'income'=>[
                        'Cancelled income cannot be updated.'
                    ],
                ]);
            }

            if($income->finance_transaction_id){
                foreach([
                    'income_account_id',
                    'receive_account_id',
                    'amount',
                    'income_date',
                ] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->changed(
                            $income->{$field},
                            $data[$field]
                        )
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Posted financial data cannot be changed. Cancel the income and create a new record instead.'
                            ],
                        ]);
                    }
                }
            }

            $update=[];

            foreach([
                'member_id',
                'reference',
                'description',
            ] as $field){
                if(array_key_exists($field,$data)){
                    $update[$field]=$data[$field];
                }
            }

            foreach([
                'reference',
                'description',
            ] as $field){
                if(array_key_exists($field,$update)){
                    $update[$field]=$this->nullableString(
                        $update[$field]
                    );
                }
            }

            $income->update($update);

            return $this->freshIncome($income);
        });
    }

    public function cancel(
        Income $income,
        string $reason,
        int $userId
    ): Income{
        return DB::transaction(function()use(
            $income,
            $reason,
            $userId
        ){
            $income=Income::query()
                ->whereKey($income->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($income->status==='cancelled'){
                throw ValidationException::withMessages([
                    'income'=>[
                        'Income is already cancelled.'
                    ],
                ]);
            }

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'reason'=>[
                        'Cancellation reason is required.'
                    ],
                ]);
            }

            $income->loadMissing(
                'financeTransaction.entries'
            );

            if($income->financeTransaction){
                $entries=$income
                    ->financeTransaction
                    ->entries
                    ->map(fn($entry)=>[
                        'account_id'=>$entry->account_id,
                        'debit'=>(float)$entry->credit,
                        'credit'=>(float)$entry->debit,
                        'description'=>
                            "Reversal of {$income->income_no}",
                    ])
                    ->all();

                $this->accountingService->post([
                    'idempotency_key'=>
                        "income:cancel:{$income->id}",
                    'transaction_date'=>$this->reversalDate(
                        $income->income_date
                    ),
                    'type'=>'income_reversal',
                    'source_module'=>'income',
                    'source_id'=>$income->id,
                    'reference_type'=>Income::class,
                    'reference_id'=>$income->id,
                    'description'=>
                        "Cancellation of {$income->income_no}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$entries,
                ]);
            }

            $income->update([
                'status'=>'cancelled',
            ]);

            return $this->freshIncome($income);
        });
    }

    public function delete(Income $income): void
    {
        DB::transaction(function()use($income){
            $income=Income::query()
                ->whereKey($income->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($income->finance_transaction_id){
                throw ValidationException::withMessages([
                    'income'=>[
                        'Posted income cannot be deleted. Cancel it using a reversal instead.'
                    ],
                ]);
            }

            $attachment=$income->attachment;

            $income->delete();

            if($attachment){
                Storage::disk('public')->delete(
                    $attachment
                );
            }
        });
    }

    protected function incomeAccount(
        int $accountId
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->where('type','income')
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'income_account_id'=>[
                    'Selected account must be an active Income posting account.'
                ],
            ]);
        }

        return $account;
    }

    protected function cashBankAccount(
        int $accountId,
        string $field
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->where('type','asset')
            ->whereIn('sub_type',[
                'cash',
                'bank',
            ])
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                $field=>[
                    'Selected account must be an active Cash or Bank posting account.'
                ],
            ]);
        }

        return $account;
    }

    protected function freshIncome(
        Income $income
    ): Income{
        return $income->fresh([
            'member.user',
            'incomeAccount',
            'receiveAccount',
            'financeTransaction.entries.account',
            'creator',
        ]);
    }

    protected function reversalDate(\DateTimeInterface|string|null $originalDate): string
    {
        $today=now()->toDateString();

        if(!$originalDate){
            return $today;
        }

        $date=$originalDate instanceof \DateTimeInterface
            ?$originalDate->format('Y-m-d')
            :(string)$originalDate;

        return $date>$today?$date:$today;
    }

    protected function changed(
        mixed $current,
        mixed $new
    ): bool{
        if($current instanceof \DateTimeInterface){
            return $current->format('Y-m-d')!==
                (string)$new;
        }

        if(is_numeric($current)&&is_numeric($new)){
            return round((float)$current,2)!==
                round((float)$new,2);
        }

        return (string)($current??'')!==
            (string)($new??'');
    }

    protected function nullableString(
        mixed $value
    ): ?string{
        if($value===null){
            return null;
        }

        $value=trim((string)$value);

        return $value===''?null:$value;
    }

    private function generateNumber(): string
    {
        $month=now()->format('Ym');

        $codePrefix=trim((string)setting('income_code_prefix','INC'));
        $codePrefix=$codePrefix!==''?strtoupper($codePrefix):'INC';

        $prefix="{$codePrefix}-{$month}-";

        return $this->numberSequenceService->next(
            key:"income:{$month}",
            prefix:$prefix,
            digits:6,
            initialValue:function()use($prefix){
                $last=Income::query()
                    ->where(
                        'income_no',
                        'like',
                        $prefix.'%'
                    )
                    ->orderByDesc('id')
                    ->value('income_no');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }
}