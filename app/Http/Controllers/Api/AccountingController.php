<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountService;
use App\Services\BalanceSheetService;
use App\Services\FinanceDashboardService;
use App\Services\GeneralLedgerService;
use App\Services\ProfitLossService;
use App\Services\TrialBalanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\ApprovalService;
use Illuminate\Support\Facades\DB;
use App\Models\ApprovalRequest;

class AccountingController extends Controller
{
    public function __construct(
        private AccountService $accountService,
        private GeneralLedgerService $generalLedgerService,
        private TrialBalanceService $trialBalanceService,
        private BalanceSheetService $balanceSheetService,
        private FinanceDashboardService $financeDashboardService,
        private ProfitLossService $profitLossService,
        private ApprovalService $approvalService
    ) {}

    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data' => $this->financeDashboardService->dashboard(),
        ]);
    }

    public function ledgerAccounts()
    {
        return response()->json([
            'success' => true,
            'data' => $this->generalLedgerService->accounts(),
        ]);
    }

    public function ledger(Request $request, Account $account)
    {
        return response()->json([
            'success' => true,
            'data' => $this->generalLedgerService->ledger(
                $account,
                $request->validate([
                    'from' => 'nullable|date_format:Y-m-d',
                    'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
                    'page' => 'nullable|integer|min:1',
                    'per_page' => 'nullable|integer|min:5|max:100',
                ])
            ),
        ]);
    }

    public function trialBalance(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->trialBalanceService->report(
                $request->validate([
                    'as_of' => 'nullable|date_format:Y-m-d',
                    'search' => 'nullable|string|max:150',
                    'type' => 'nullable|in:asset,liability,equity,income,expense',
                    'show_zero' => 'nullable|boolean',
                    'page' => 'nullable|integer|min:1',
                    'per_page' => 'nullable|integer|min:5|max:100',
                ])
            ),
        ]);
    }

    public function balanceSheet(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->balanceSheetService->report(
                $request->validate([
                    'as_of' => 'nullable|date_format:Y-m-d',
                ])
            ),
        ]);
    }

    public function profitLoss(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->profitLossService->report(
                $request->validate([
                    'from' => 'nullable|date_format:Y-m-d',
                    'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
                ])
            ),
        ]);
    }

    public function accounts(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->accountService->paginate(
                $request->validate([
                    'search' => 'nullable|string|max:150',
                    'type' => [
                        'nullable',
                        Rule::in([
                            'asset',
                            'liability',
                            'equity',
                            'income',
                            'expense',
                        ]),
                    ],
                    'is_active' => 'nullable|in:0,1,true,false',
                    'parent_id' => 'nullable|integer|exists:accounts,id',
                    'page' => 'nullable|integer|min:1',
                    'per_page' => 'nullable|integer|min:5|max:100',
                ])
            ),
        ]);
    }

    public function account(Account $account)
    {
        $account
            ->load([
                'parent:id,code,name,type',
                'children:id,parent_id,code,name,type,is_active',
            ])
            ->loadCount([
                'children',
                'postedEntries',
            ])
            ->loadSum(
                'postedEntries as total_debit',
                'debit'
            )
            ->loadSum(
                'postedEntries as total_credit',
                'credit'
            );

        $debit = round(
            (float)($account->total_debit ?? 0),
            2
        );

        $credit = round(
            (float)($account->total_credit ?? 0),
            2
        );

        return response()->json([
            'success' => true,
            'data' => [
                ...$account->toArray(),

                'posted_entries_count' =>
                (int)($account->posted_entries_count ?? 0),

                'total_debit' => $debit,
                'total_credit' => $credit,

                'balance' => $account->calculateBalance(
                    $debit,
                    $credit
                ),

                'is_posting' =>
                $account->children_count === 0,
            ],
        ]);
    }

    public function accountSummary()
    {
        return response()->json([
            'success' => true,
            'data' => $this->accountService->summary(),
        ]);
    }

    public function accountOptions(?Account $account = null)
    {
        return response()->json([
            'success' => true,
            'data' => $this->accountService->options(
                $account
            ),
        ]);
    }

    public function storeAccount(Request $request)
    {
        $data = $this->validateAccount($request);

        $account = DB::transaction(function () use ($data) {
            $account = $this->accountService->create($data);

            $this->approvalService->createRequest(
                $account,
                'Account',
                'create',
                auth()->id(),
                'New account creation requires approval.'
            );

            return $account;
        });

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully and sent for approval.',
            'data' => $account->fresh([
                'parent:id,code,name,type',
            ]),
        ], 201);
    }

    public function updateAccount(
        Request $request,
        Account $account
    ) {
        $account = $this->accountService->update(
            $account,
            $this->validateAccount(
                $request,
                $account,
                true
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully.',
            'data' => $account,
        ]);
    }

    public function toggleAccount(Account $account)
    {
        $account = $this->accountService->toggle(
            $account
        );

        return response()->json([
            'success' => true,
            'message' => $account->is_active
                ? 'Account activated successfully.'
                : 'Account deactivated successfully.',
            'data' => $account,
        ]);
    }

    public function destroyAccount(Account $account)
    {
        $this->assertNoPendingApproval($account);

        $approvalRequest = $this->approvalService->createRequest(
            $account,
            'Account',
            'delete',
            auth()->id(),
            'Account deletion requires approval.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Delete request submitted and sent for approval.',
            'data' => $approvalRequest,
        ]);
    }

    private function assertNoPendingApproval(Account $account): void
    {
        $hasPending = ApprovalRequest::query()
            ->where('approvable_type', $account->getMorphClass())
            ->where('approvable_id', $account->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'account' => [
                    'This account already has a pending approval request. Please wait until it is resolved.'
                ],
            ]);
        }
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
                'exists:accounts,id',
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
                ),
            ],

            'name' => [
                $required,
                'string',
                'max:150',
            ],

            'type' => [
                $required,
                Rule::in([
                    'asset',
                    'liability',
                    'equity',
                    'income',
                    'expense',
                ]),
            ],

            'sub_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'opening_balance' => [
                'nullable',
                'numeric',
                'min:-9999999999999.99',
                'max:9999999999999.99',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ]);
    }
}
