<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(
        protected AccountingService $accounting
    ){}

    public function create(array $data,int $userId): Expense
    {
        return DB::transaction(function()use($data,$userId){
            $expenseAccount=Account::query()
                ->whereKey($data['expense_account_id'])
                ->where('type','expense')
                ->where('is_active',true)
                ->first();

            if(!$expenseAccount){
                throw ValidationException::withMessages([
                    'expense_account_id'=>['Invalid expense account.'],
                ]);
            }

            $paymentAccount=Account::query()
                ->whereKey($data['payment_account_id'])
                ->whereIn('sub_type',['cash','bank'])
                ->where('is_active',true)
                ->first();

            if(!$paymentAccount){
                throw ValidationException::withMessages([
                    'payment_account_id'=>['Payment account must be an active Cash or Bank account.'],
                ]);
            }

            $expense=Expense::create([
                'expense_no'=>$this->generateNumber(),
                'expense_account_id'=>$expenseAccount->id,
                'payment_account_id'=>$paymentAccount->id,
                'amount'=>$data['amount'],
                'expense_date'=>$data['expense_date'],
                'payee'=>$data['payee']??null,
                'reference'=>$data['reference']??null,
                'description'=>$data['description']??null,
                'attachment'=>$data['attachment']??null,
                'created_by'=>$userId,
                'status'=>'posted',
            ]);

            $journal=$this->accounting->post([
                'transaction_date'=>$expense->expense_date->toDateString(),
                'type'=>'expense',
                'source_module'=>'expense',
                'source_id'=>$expense->id,
                'reference_type'=>Expense::class,
                'reference_id'=>$expense->id,
                'description'=>$expense->description??"Expense {$expense->expense_no}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$expenseAccount->id,
                        'debit'=>$expense->amount,
                        'credit'=>0,
                        'description'=>'Expense recognized',
                    ],
                    [
                        'account_id'=>$paymentAccount->id,
                        'debit'=>0,
                        'credit'=>$expense->amount,
                        'description'=>'Expense payment',
                    ],
                ],
            ]);

            $expense->update([
                'finance_transaction_id'=>$journal->id,
            ]);

            return $expense->fresh([
                'expenseAccount',
                'paymentAccount',
                'financeTransaction.entries.account',
                'creator',
            ]);
        });
    }

    public function cancel(Expense $expense,string $reason,int $userId): Expense
    {
        return DB::transaction(function()use($expense,$reason,$userId){
            $expense=Expense::query()
                ->whereKey($expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($expense->status==='cancelled'){
                throw ValidationException::withMessages([
                    'expense'=>['Expense is already cancelled.'],
                ]);
            }

            $expense->loadMissing('financeTransaction.entries');

            if($expense->financeTransaction){
                $reversalEntries=$expense->financeTransaction->entries->map(fn($entry)=>[
                    'account_id'=>$entry->account_id,
                    'debit'=>$entry->credit,
                    'credit'=>$entry->debit,
                    'description'=>"Reversal of {$expense->expense_no}",
                ])->all();

                $this->accounting->post([
                    'transaction_date'=>now()->toDateString(),
                    'type'=>'expense_reversal',
                    'source_module'=>'expense',
                    'source_id'=>$expense->id,
                    'reference_type'=>Expense::class,
                    'reference_id'=>$expense->id,
                    'description'=>"Cancellation of {$expense->expense_no}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$reversalEntries,
                ]);
            }

            $expense->update([
                'status'=>'cancelled',
            ]);

            return $expense->fresh();
        });
    }

    private function generateNumber(): string
    {
        $prefix='EXP-'.now()->format('Ym').'-';

        $last=Expense::query()
            ->where('expense_no','like',$prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('expense_no');

        $next=$last?((int)substr($last,-6))+1:1;

        return $prefix.str_pad($next,6,'0',STR_PAD_LEFT);
    }
}