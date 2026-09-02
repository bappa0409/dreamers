<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected NumberSequenceService $numberSequenceService
    ){}

    public function create(
        array $data,
        int $userId
    ): Expense{
        return DB::transaction(function()use(
            $data,
            $userId
        ){
            $expenseAccount=$this->expenseAccount(
                (int)$data['expense_account_id']
            );

            $paymentAccount=$this->cashBankAccount(
                (int)$data['payment_account_id']
            );

            $amount=round(
                (float)$data['amount'],
                2
            );

            if($amount<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Expense amount must be greater than zero.'
                    ],
                ]);
            }

            $expense=Expense::create([
                'expense_no'=>$this->generateNumber(),
                'expense_account_id'=>$expenseAccount->id,
                'payment_account_id'=>$paymentAccount->id,
                'amount'=>$amount,
                'expense_date'=>$data['expense_date'],
                'payee'=>$this->nullableString(
                    $data['payee']??null
                ),
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
            // Expense.create approval request is approved — see
            // finalizeApproval().
            return $this->freshExpense($expense);
        });
    }

    /**
     * Called by ApprovalService once the Expense.create request is
     * approved. Posts the actual double-entry ledger transaction and
     * marks the expense as posted.
     */
    public function finalizeApproval(
        Expense $expense,
        array $decisionData,
        int $approvedBy
    ): Expense{
        return DB::transaction(function()use(
            $expense,
            $approvedBy
        ){
            $expense=Expense::query()
                ->whereKey($expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($expense->status!=='pending_approval'){
                throw ValidationException::withMessages([
                    'expense'=>[
                        'This expense is not awaiting approval.'
                    ],
                ]);
            }

            $journal=$this->accountingService->post([
                'idempotency_key'=>"expense:post:{$expense->id}",
                'transaction_date'=>$expense
                    ->expense_date
                    ->toDateString(),
                'type'=>'expense',
                'source_module'=>'expense',
                'source_id'=>$expense->id,
                'reference_type'=>Expense::class,
                'reference_id'=>$expense->id,
                'description'=>$expense->description
                    ??"Expense {$expense->expense_no}",
                'user_id'=>$approvedBy,
                'entries'=>[
                    [
                        'account_id'=>$expense->expense_account_id,
                        'debit'=>$expense->amount,
                        'credit'=>0,
                        'description'=>'Expense recognized',
                    ],
                    [
                        'account_id'=>$expense->payment_account_id,
                        'debit'=>0,
                        'credit'=>$expense->amount,
                        'description'=>'Expense payment',
                    ],
                ],
            ]);

            $expense->update([
                'finance_transaction_id'=>$journal->id,
                'status'=>'posted',
            ]);

            return $this->freshExpense($expense);
        });
    }

    /**
     * Called by ApprovalService when the Expense.create request is
     * rejected. The expense never gets a ledger entry.
     */
    public function finalizeRejection(
        Expense $expense,
        string $reason,
        ?int $rejectedBy
    ): Expense{
        $expense=Expense::query()
            ->whereKey($expense->id)
            ->lockForUpdate()
            ->firstOrFail();

        if($expense->status==='pending_approval'){
            $expense->update([
                'status'=>'rejected',
            ]);
        }

        return $this->freshExpense($expense);
    }

    /**
     * Called by ApprovalService when the Expense.create request is
     * cancelled/withdrawn before a decision is made.
     */
    public function finalizeCancellation(Expense $expense): Expense
    {
        $expense=Expense::query()
            ->whereKey($expense->id)
            ->lockForUpdate()
            ->firstOrFail();

        if($expense->status==='pending_approval'){
            $expense->update([
                'status'=>'cancelled',
            ]);
        }

        return $this->freshExpense($expense);
    }

    public function update(
        Expense $expense,
        array $data
    ): Expense{
        return DB::transaction(function()use(
            $expense,
            $data
        ){
            $expense=Expense::query()
                ->whereKey($expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($expense->status==='cancelled'){
                throw ValidationException::withMessages([
                    'expense'=>[
                        'Cancelled expense cannot be updated.'
                    ],
                ]);
            }

            if($expense->finance_transaction_id){
                foreach([
                    'expense_account_id',
                    'payment_account_id',
                    'amount',
                    'expense_date',
                ] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->changed(
                            $expense->{$field},
                            $data[$field]
                        )
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Posted financial data cannot be changed. Cancel the expense and create a new record instead.'
                            ],
                        ]);
                    }
                }
            }

            $update=[];

            foreach([
                'payee',
                'reference',
                'description',
            ] as $field){
                if(array_key_exists($field,$data)){
                    $update[$field]=$data[$field];
                }
            }

            foreach([
                'payee',
                'reference',
                'description',
            ] as $field){
                if(array_key_exists($field,$update)){
                    $update[$field]=$this->nullableString(
                        $update[$field]
                    );
                }
            }

            $expense->update($update);

            return $this->freshExpense($expense);
        });
    }

    public function cancel(
        Expense $expense,
        string $reason,
        int $userId
    ): Expense{
        return DB::transaction(function()use(
            $expense,
            $reason,
            $userId
        ){
            $expense=Expense::query()
                ->whereKey($expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($expense->status==='cancelled'){
                throw ValidationException::withMessages([
                    'expense'=>[
                        'Expense is already cancelled.'
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

            $expense->loadMissing(
                'financeTransaction.entries'
            );

            if($expense->financeTransaction){
                $entries=$expense
                    ->financeTransaction
                    ->entries
                    ->map(fn($entry)=>[
                        'account_id'=>$entry->account_id,
                        'debit'=>(float)$entry->credit,
                        'credit'=>(float)$entry->debit,
                        'description'=>
                            "Reversal of {$expense->expense_no}",
                    ])
                    ->all();

                $this->accountingService->post([
                    'idempotency_key'=>
                        "expense:cancel:{$expense->id}",
                    'transaction_date'=>$this->reversalDate(
                        $expense->expense_date
                    ),
                    'type'=>'expense_reversal',
                    'source_module'=>'expense',
                    'source_id'=>$expense->id,
                    'reference_type'=>Expense::class,
                    'reference_id'=>$expense->id,
                    'description'=>
                        "Cancellation of {$expense->expense_no}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$entries,
                ]);
            }

            $expense->update([
                'status'=>'cancelled',
            ]);

            return $this->freshExpense($expense);
        });
    }

    public function delete(
        Expense $expense
    ): void{
        DB::transaction(function()use($expense){
            $expense=Expense::query()
                ->whereKey($expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($expense->finance_transaction_id){
                throw ValidationException::withMessages([
                    'expense'=>[
                        'Posted expense cannot be deleted. Cancel it using a reversal instead.'
                    ],
                ]);
            }

            $attachment=$expense->attachment;

            $expense->delete();

            if($attachment){
                Storage::disk('public')->delete(
                    $attachment
                );
            }
        });
    }

    protected function expenseAccount(
        int $accountId
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->where('type','expense')
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'expense_account_id'=>[
                    'Selected account must be an active Expense posting account.'
                ],
            ]);
        }

        return $account;
    }

    protected function cashBankAccount(
        int $accountId
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
                'payment_account_id'=>[
                    'Payment account must be an active Cash or Bank posting account.'
                ],
            ]);
        }

        return $account;
    }

    protected function freshExpense(
        Expense $expense
    ): Expense{
        return $expense->fresh([
            'expenseAccount',
            'paymentAccount',
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
        $prefix="EXP-{$month}-";

        return $this->numberSequenceService->next(
            key:"expense:{$month}",
            prefix:$prefix,
            digits:6,
            initialValue:function()use($prefix){
                $last=Expense::query()
                    ->where(
                        'expense_no',
                        'like',
                        $prefix.'%'
                    )
                    ->orderByDesc('id')
                    ->value('expense_no');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }
}