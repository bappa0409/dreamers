<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountingService;

class AccountingController extends Controller
{
    public function __construct(
        private AccountingService $accountingService
    ) {}

    public function accounts()
    {
        return response()->json([
            'data' => $this->accountingService->getAccountSummary()
        ]);
    }

    public function account(Account $account)
    {
        return response()->json([
            'data' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'opening_balance' => (float) $account->opening_balance,
                'balance' => $this->accountingService
                    ->getAccountBalance($account),
            ],
        ]);
    }

    public function cashBank()
    {
        return response()->json([
            'data' => $this->accountingService
                ->getCashBankSummary(),
        ]);
    }

    public function incomeExpense()
{
    return response()->json([
        'data' => $this->accountingService->getIncomeExpenseSummary(),
    ]);
}

public function ledger(Account $account)
{
    return response()->json([
        'data' => $this->accountingService->getLedger($account),
    ]);
}

public function trialBalance()
{
    return response()->json([
        'data' => $this->accountingService->getTrialBalance(),
    ]);
}

public function profitAndLoss()
{
    return response()->json([
        'data' => $this->accountingService->getProfitAndLoss(),
    ]);
}

public function dashboard()
{
    return response()->json([
        'data' => $this->accountingService->getFinanceDashboard(),
    ]);
}
}