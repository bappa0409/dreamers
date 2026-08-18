<?php

namespace App\Services;

use App\Models\Account;
use App\Models\TransactionEntry;
use App\Models\Transaction;

class AccountingService
{
    public function getAccountBalance(Account $account): float
    {
        $debit = TransactionEntry::where('account_id', $account->id)
            ->whereHas('transaction', function ($query) {
                $query->where('status', 'posted');
            })
            ->sum('debit');

        $credit = TransactionEntry::where('account_id', $account->id)
            ->whereHas('transaction', function ($query) {
                $query->where('status', 'posted');
            })
            ->sum('credit');

        return match ($account->type) {
            'asset', 'expense' => (float) (
                $account->opening_balance + $debit - $credit
            ),

            'liability', 'income', 'equity' => (float) (
                $account->opening_balance + $credit - $debit
            ),

            default => 0,
        };
    }

    public function getAccountSummary(): array
    {
        $accounts = Account::where('is_active', true)
            ->get();

        return $accounts->map(function ($account) {
            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'opening_balance' => (float) $account->opening_balance,
                'balance' => $this->getAccountBalance($account),
            ];
        })->values()->toArray();
    }

    public function validateDoubleEntry(array $entries): bool
    {
        $totalDebit = collect($entries)->sum(function ($entry) {
            return (float) ($entry['debit'] ?? 0);
        });

        $totalCredit = collect($entries)->sum(function ($entry) {
            return (float) ($entry['credit'] ?? 0);
        });

        return round($totalDebit, 2) === round($totalCredit, 2);
    }

    public function validateAndPost(Transaction $transaction): void
    {
        $entries = $transaction->entries;

        if ($entries->count() < 2) {
            throw new \Exception('A transaction must have at least two entries.');
        }

        $totalDebit = round((float) $entries->sum('debit'), 2);
        $totalCredit = round((float) $entries->sum('credit'), 2);

        if ($totalDebit <= 0 || $totalCredit <= 0) {
            throw new \Exception('Transaction must contain both debit and credit amounts.');
        }

        if ($totalDebit !== $totalCredit) {
            throw new \Exception(
                "Double-entry validation failed. Debit: {$totalDebit}, Credit: {$totalCredit}."
            );
        }

        $transaction->update([
            'status' => 'posted',
        ]);
    }

    public function getCashBankSummary(): array
    {
        $accounts = Account::where('is_active', true)
            ->whereIn('type', ['cash', 'bank'])
            ->get();

        $data = $accounts->map(function ($account) {
            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'opening_balance' => (float) $account->opening_balance,
                'balance' => $this->getAccountBalance($account),
            ];
        })->values();

        return [
            'accounts' => $data->toArray(),
            'total_cash' => (float) $data
                ->where('type', 'cash')
                ->sum('balance'),
            'total_bank' => (float) $data
                ->where('type', 'bank')
                ->sum('balance'),
            'total_cash_bank' => (float) $data->sum('balance'),
        ];
    }

    public function getIncomeExpenseSummary(): array
{
    $incomeAccounts = Account::where('is_active', true)
        ->where('type', 'income')
        ->get();

    $expenseAccounts = Account::where('is_active', true)
        ->where('type', 'expense')
        ->get();

    $income = $incomeAccounts->map(function ($account) {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'balance' => $this->getAccountBalance($account),
        ];
    })->values();

    $expense = $expenseAccounts->map(function ($account) {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'balance' => $this->getAccountBalance($account),
        ];
    })->values();

    $totalIncome = (float) $income->sum('balance');
    $totalExpense = (float) $expense->sum('balance');

    return [
        'income_accounts' => $income->toArray(),
        'expense_accounts' => $expense->toArray(),
        'total_income' => $totalIncome,
        'total_expense' => $totalExpense,
        'net_profit_loss' => $totalIncome - $totalExpense,
    ];
}

public function getLedger(Account $account): array
{
    $entries = TransactionEntry::with('transaction')
        ->where('account_id', $account->id)
        ->whereHas('transaction', function ($query) {
            $query->where('status', 'posted');
        })
        ->get()
        ->sortBy(function ($entry) {
            return $entry->transaction->transaction_date
                . '-' . $entry->transaction->id;
        })
        ->values();

    $runningBalance = (float) $account->opening_balance;

    $ledger = $entries->map(function ($entry) use ($account, &$runningBalance) {

        $debit = (float) $entry->debit;
        $credit = (float) $entry->credit;

        if (in_array($account->type, ['asset', 'expense'])) {
            $runningBalance += $debit - $credit;
        } else {
            $runningBalance += $credit - $debit;
        }

        return [
            'entry_id' => $entry->id,
            'transaction_id' => $entry->transaction_id,
            'transaction_no' => $entry->transaction->transaction_no,
            'date' => $entry->transaction->transaction_date,
            'description' => $entry->description
                ?? $entry->transaction->description,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => round($runningBalance, 2),
        ];
    });

    return [
        'account' => [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type,
            'opening_balance' => (float) $account->opening_balance,
        ],
        'entries' => $ledger->toArray(),
        'closing_balance' => round($runningBalance, 2),
    ];
}

public function getTrialBalance(): array
{
    $accounts = Account::where('is_active', true)
        ->orderBy('code')
        ->get();

    $data = $accounts->map(function ($account) {
        $balance = $this->getAccountBalance($account);

        $debit = 0;
        $credit = 0;

        if (in_array($account->type, ['asset', 'expense'])) {
            if ($balance >= 0) {
                $debit = $balance;
            } else {
                $credit = abs($balance);
            }
        } else {
            if ($balance >= 0) {
                $credit = $balance;
            } else {
                $debit = abs($balance);
            }
        }

        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type,
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
        ];
    })->values();

    $totalDebit = (float) $data->sum('debit');
    $totalCredit = (float) $data->sum('credit');

    return [
        'accounts' => $data->toArray(),
        'total_debit' => round($totalDebit, 2),
        'total_credit' => round($totalCredit, 2),
        'difference' => round($totalDebit - $totalCredit, 2),
        'is_balanced' => round($totalDebit, 2) === round($totalCredit, 2),
    ];
}

public function getProfitAndLoss(): array
{
    $incomeAccounts = Account::where('is_active', true)
        ->where('type', 'income')
        ->orderBy('code')
        ->get();

    $expenseAccounts = Account::where('is_active', true)
        ->where('type', 'expense')
        ->orderBy('code')
        ->get();

    $income = $incomeAccounts->map(function ($account) {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'amount' => round(
                $this->getAccountBalance($account),
                2
            ),
        ];
    })->values();

    $expense = $expenseAccounts->map(function ($account) {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'amount' => round(
                $this->getAccountBalance($account),
                2
            ),
        ];
    })->values();

    $totalIncome = round(
        (float) $income->sum('amount'),
        2
    );

    $totalExpense = round(
        (float) $expense->sum('amount'),
        2
    );

    $netProfitLoss = round(
        $totalIncome - $totalExpense,
        2
    );

    return [
        'income' => $income->toArray(),
        'total_income' => $totalIncome,

        'expense' => $expense->toArray(),
        'total_expense' => $totalExpense,

        'net_profit_loss' => $netProfitLoss,

        'status' => match (true) {
            $netProfitLoss > 0 => 'profit',
            $netProfitLoss < 0 => 'loss',
            default => 'break_even',
        },
    ];
}

public function getFinanceDashboard(): array
{
    $cashBank = $this->getCashBankSummary();
    $incomeExpense = $this->getIncomeExpenseSummary();
    $trialBalance = $this->getTrialBalance();

    $recentTransactions = Transaction::with('entries.account')
        ->where('status', 'posted')
        ->latest('transaction_date')
        ->latest('id')
        ->limit(10)
        ->get()
        ->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'transaction_no' => $transaction->transaction_no,
                'date' => $transaction->transaction_date,
                'type' => $transaction->type,
                'description' => $transaction->description,
                'status' => $transaction->status,
                'entries' => $transaction->entries->map(function ($entry) {
                    return [
                        'account_id' => $entry->account_id,
                        'account_code' => $entry->account?->code,
                        'account_name' => $entry->account?->name,
                        'debit' => (float) $entry->debit,
                        'credit' => (float) $entry->credit,
                    ];
                })->values()->toArray(),
            ];
        })
        ->values()
        ->toArray();

    return [
        'cash' => $cashBank['total_cash'],
        'bank' => $cashBank['total_bank'],
        'cash_bank_total' => $cashBank['total_cash_bank'],

        'total_income' => $incomeExpense['total_income'],
        'total_expense' => $incomeExpense['total_expense'],
        'net_profit_loss' => $incomeExpense['net_profit_loss'],
        'profit_loss_status' => $incomeExpense['net_profit_loss'] > 0
            ? 'profit'
            : ($incomeExpense['net_profit_loss'] < 0 ? 'loss' : 'break_even'),

        'trial_balance' => [
            'total_debit' => $trialBalance['total_debit'],
            'total_credit' => $trialBalance['total_credit'],
            'difference' => $trialBalance['difference'],
            'is_balanced' => $trialBalance['is_balanced'],
        ],

        'recent_transactions' => $recentTransactions,
    ];
}
}
