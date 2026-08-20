<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\GeneralLedgerService;
use App\Services\TrialBalanceService;
use App\Services\BalanceSheetService;
use App\Services\FinanceDashboardService;
use App\Services\ProfitLossService;

class AccountingController extends Controller
{
    public function __construct(
        private AccountService $accountService,
    private GeneralLedgerService $generalLedgerService,
    private TrialBalanceService $trialBalanceService,
    private BalanceSheetService $balanceSheetService,
    private FinanceDashboardService $financeDashboardService,
    private ProfitLossService $profitLossService
    ) {}

    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data' => $this->financeDashboardService
                ->dashboard()
        ]);
    }

    public function ledgerAccounts()
    {
        return response()->json([
            'success' => true,
            'data' => $this->generalLedgerService
                ->accounts()
        ]);
    }

    public function ledger(
        Request $request,
        Account $account
    ) {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'per_page' => 'nullable|integer|min:5|max:100'
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->generalLedgerService
                ->ledger(
                    $account,
                    $validated
                )
        ]);
    }

    public function trialBalance(Request $request)
    {
        $validated = $request->validate([
            'as_of' => 'nullable|date',
            'search' => 'nullable|string|max:150',
            'type' => 'nullable|in:asset,liability,equity,income,expense',
            'show_zero' => 'nullable|boolean',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:100'
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->trialBalanceService
                ->report($validated)
        ]);
    }
    public function balanceSheet(Request $request)
    {
        $validated = $request->validate([
            'as_of' => 'nullable|date'
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->balanceSheetService
                ->report($validated)
        ]);
    }
    public function profitLoss(Request $request)
{
    $validated=$request->validate([
        'from'=>'nullable|date',
        'to'=>'nullable|date|after_or_equal:from'
    ]);

    return response()->json([
        'success'=>true,
        'data'=>$this->profitLossService
            ->report($validated)
    ]);
}

    public function accounts(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:150',
            'type' => [
                'nullable',
                Rule::in([
                    'asset',
                    'liability',
                    'equity',
                    'income',
                    'expense'
                ])
            ],
            'is_active' => 'nullable|in:0,1,true,false',
            'parent_id' => 'nullable|integer|exists:accounts,id',
            'per_page' => 'nullable|integer|min:5|max:100'
        ]);

        return response()->json([
            'data' => $this->accountService
                ->paginate($validated)
        ]);
    }

    public function account(Account $account)
    {
        $account->load([
            'parent:id,code,name,type',
            'children:id,parent_id,code,name,type,is_active'
        ])->loadCount([
            'children',
            'entries'
        ]);

        $debit = (float)$account
            ->entries()
            ->sum('debit');

        $credit = (float)$account
            ->entries()
            ->sum('credit');

        $balance = in_array(
            $account->type,
            ['asset', 'expense'],
            true
        )
            ? (float)$account->opening_balance +
            $debit -
            $credit
            : (float)$account->opening_balance +
            $credit -
            $debit;

        return response()->json([
            'data' => [
                ...$account->toArray(),
                'total_debit' => $debit,
                'total_credit' => $credit,
                'balance' => $balance,
                'is_posting' => $account
                    ->children_count === 0
            ]
        ]);
    }

    public function accountSummary()
    {
        return response()->json([
            'data' => $this->accountService
                ->summary()
        ]);
    }

    public function accountOptions(
        ?Account $account = null
    ) {
        return response()->json([
            'data' => $this->accountService
                ->options($account)
        ]);
    }

    public function storeAccount(Request $request)
    {
        $validated = $this->validateAccount(
            $request
        );

        $account = $this->accountService
            ->create($validated);

        return response()->json([
            'message' => 'Account created successfully.',
            'data' => $account
        ], 201);
    }

    public function updateAccount(
        Request $request,
        Account $account
    ) {
        $validated = $this->validateAccount(
            $request,
            $account,
            true
        );

        $account = $this->accountService
            ->update(
                $account,
                $validated
            );

        return response()->json([
            'message' => 'Account updated successfully.',
            'data' => $account
        ]);
    }

    public function toggleAccount(
        Account $account
    ) {
        $account = $this->accountService
            ->toggle($account);

        return response()->json([
            'message' => $account->is_active
                ? 'Account activated successfully.'
                : 'Account deactivated successfully.',
            'data' => $account
        ]);
    }

    public function destroyAccount(
        Account $account
    ) {
        $this->accountService
            ->delete($account);

        return response()->json([
            'message' => 'Account deleted successfully.'
        ]);
    }

    private function validateAccount(
        Request $request,
        ?Account $account = null,
        bool $partial = false
    ): array {
        $required = $partial
            ? 'sometimes'
            : 'required';

        return $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                'exists:accounts,id'
            ],
            'code' => [
                $required,
                'string',
                'max:30',
                Rule::unique(
                    'accounts',
                    'code'
                )->ignore(
                    $account?->id
                )
            ],
            'name' => [
                $required,
                'string',
                'max:150'
            ],
            'type' => [
                $required,
                Rule::in([
                    'asset',
                    'liability',
                    'equity',
                    'income',
                    'expense'
                ])
            ],
            'sub_type' => [
                'nullable',
                'string',
                'max:50'
            ],
            'opening_balance' => [
                'nullable',
                'numeric',
                'min:-9999999999999.99',
                'max:9999999999999.99'
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
            'description' => [
                'nullable',
                'string',
                'max:3000'
            ]
        ]);
    }
}
