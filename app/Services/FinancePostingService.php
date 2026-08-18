<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinancePostingService
{
    public function postTellerReceive(
        float $amount,
        int $userId,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): Transaction {
        return DB::transaction(function () use (
            $amount,
            $userId,
            $description,
            $referenceType,
            $referenceId
        ) {

            $cashAccount = Account::where('code', '1000')
                ->where('is_active', true)
                ->first();

            $incomeAccount = Account::where('code', '4000')
                ->where('is_active', true)
                ->first();

            if (!$cashAccount || !$incomeAccount) {
                throw ValidationException::withMessages([
                    'finance' => 'Cash or Income account not found.',
                ]);
            }

            $transaction = Transaction::create([
                'transaction_no' => $this->generateTransactionNo(),
                'transaction_date' => now()->toDateString(),
                'type' => 'income',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $userId,
                'description' => $description,
                'status' => 'posted',
            ]);

            TransactionEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $cashAccount->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Cash received',
            ]);

            TransactionEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $incomeAccount->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => $description,
            ]);

            return $transaction->load('entries');
        });
    }

    public function postTellerPayment(
        float $amount,
        int $userId,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): Transaction {
        return DB::transaction(function () use (
            $amount,
            $userId,
            $description,
            $referenceType,
            $referenceId
        ) {

            $cashAccount = Account::where('code', '1000')
                ->where('is_active', true)
                ->first();

            $expenseAccount = Account::where('code', '5000')
                ->where('is_active', true)
                ->first();

            if (!$cashAccount || !$expenseAccount) {
                throw ValidationException::withMessages([
                    'finance' => 'Cash or Expense account not found.',
                ]);
            }

            $transaction = Transaction::create([
                'transaction_no' => $this->generateTransactionNo(),
                'transaction_date' => now()->toDateString(),
                'type' => 'expense',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $userId,
                'description' => $description,
                'status' => 'posted',
            ]);

            TransactionEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $expenseAccount->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
            ]);

            TransactionEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $cashAccount->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Cash payment',
            ]);

            return $transaction->load('entries');
        });
    }

    private function generateTransactionNo(): string
    {
        $last = Transaction::latest('id')->first();

        $nextNumber = $last
            ? $last->id + 1
            : 1;

        return 'TXN-' . str_pad(
            $nextNumber,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}