<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountService
{
    private const TYPES = [
        'asset',
        'liability',
        'equity',
        'income',
        'expense',
    ];

    public function __construct(
        private FinanceDashboardService $financeDashboardService
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $paginator = Account::query()
            ->with([
                'parent:id,code,name,type',
            ])
            ->withCount([
                'children',
                'postedEntries',
            ])
            ->withSum(
                'postedEntries as total_debit',
                'debit'
            )
            ->withSum(
                'postedEntries as total_credit',
                'credit'
            )
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $search = trim($search);

                    $query->where(function ($q) use ($search) {
                        $q->where(
                            'code',
                            'like',
                            "%{$search}%"
                        )
                            ->orWhere(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'sub_type',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'description',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                $filters['type'] ?? null,
                fn($query, $type) => $query->where(
                    'type',
                    $type
                )
            )
            ->when(
                array_key_exists(
                    'is_active',
                    $filters
                ) &&
                    $filters['is_active'] !== '',
                fn($query) => $query->where(
                    'is_active',
                    filter_var(
                        $filters['is_active'],
                        FILTER_VALIDATE_BOOLEAN
                    )
                )
            )
            ->when(
                $filters['parent_id'] ?? null,
                fn($query, $parentId) => $query->where(
                    'parent_id',
                    $parentId
                )
            )
            ->orderBy('code')
            ->paginate(
                min(
                    max(
                        (int)($filters['per_page'] ?? 25),
                        5
                    ),
                    100
                )
            );

        $paginator->getCollection()->transform(
            function (Account $account) {
                $account->setAttribute(
                    'balance',
                    $account->calculateBalance(
                        (float)($account->total_debit ?? 0),
                        (float)($account->total_credit ?? 0)
                    )
                );

                return $account;
            }
        );

        return $paginator;
    }

    public function summary(): array
    {
        $rows = Account::query()
            ->selectRaw("
                COUNT(*) total,
                SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) active,
                SUM(CASE WHEN is_active=0 THEN 1 ELSE 0 END) inactive,
                SUM(CASE WHEN is_system=1 THEN 1 ELSE 0 END) system_accounts
            ")
            ->first();

        $posting = Account::query()
            ->whereDoesntHave('children')
            ->count();

        return [
            'total' => (int)($rows->total ?? 0),
            'active' => (int)($rows->active ?? 0),
            'inactive' => (int)($rows->inactive ?? 0),
            'system_accounts' => (int)($rows->system_accounts ?? 0),
            'posting_accounts' => $posting,
        ];
    }

    public function options(?Account $exclude = null): array
    {
        $query = Account::query()
            ->orderBy('code');

        if ($exclude) {
            $excludedIds = $this->descendantIds(
                $exclude
            );

            $excludedIds[] = $exclude->id;

            $query->whereNotIn(
                'id',
                array_unique(
                    $excludedIds
                )
            );
        }

        return [
            'types' => self::TYPES,
            'parents' => $query->get([
                'id',
                'parent_id',
                'code',
                'name',
                'type',
                'is_active',
                'is_system',
            ]),
        ];
    }

    public function create(array $data): Account
    {
        return DB::transaction(function () use ($data) {
            $openingBalance = round(
                (float)($data['opening_balance'] ?? 0),
                2
            );

            if (abs($openingBalance) >= 0.01) {
                throw ValidationException::withMessages([
                    'opening_balance' => [
                        'Opening balance must be posted through an opening balance journal, not directly on the account.'
                    ],
                ]);
            }

            $this->validateParent(
                $data['parent_id'] ?? null,
                $data['type']
            );

            if (!empty($data['parent_id'])) {
                $parent = Account::query()
                    ->whereKey(
                        $data['parent_id']
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $parent
                    ->postedEntries()
                    ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'parent_id' => [
                            'An account with posted journal entries cannot be converted into a parent account.'
                        ],
                    ]);
                }
            }

            $account = Account::create([
                'parent_id' => $data['parent_id'] ?? null,
                'code' => trim($data['code']),
                'name' => trim($data['name']),
                'type' => $data['type'],
                'sub_type' => $this->nullableString(
                    $data['sub_type'] ?? null
                ),
                'opening_balance' => 0,
                'is_system' => false,
                'is_active' => false,
                'approval_status' => 'pending',
                'description' => $this->nullableString(
                    $data['description'] ?? null
                ),
            ]);

            DB::afterCommit(
                fn() =>
                $this
                    ->financeDashboardService
                    ->forgetCache()
            );

            return $this->fresh(
                $account
            );
        });
    }

    public function update(
        Account $account,
        array $data
    ): Account {
        return DB::transaction(function () use (
            $account,
            $data
        ) {
            $account = Account::query()
                ->whereKey(
                    $account->id
                )
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->is_system) {
                foreach (
                    [
                        'code',
                        'type',
                        'sub_type',
                        'parent_id',
                        'opening_balance',
                    ] as $field
                ) {
                    if (
                        array_key_exists(
                            $field,
                            $data
                        ) &&
                        $this->changed(
                            $account->{$field},
                            $data[$field]
                        )
                    ) {
                        throw ValidationException::withMessages([
                            $field => [
                                'Core structure of a system account cannot be changed.'
                            ],
                        ]);
                    }
                }

                if (
                    array_key_exists(
                        'is_active',
                        $data
                    ) &&
                    !$data['is_active']
                ) {
                    throw ValidationException::withMessages([
                        'is_active' => [
                            'System accounts cannot be deactivated.'
                        ],
                    ]);
                }
            }

            if (
                array_key_exists(
                    'opening_balance',
                    $data
                ) &&
                $this->changed(
                    $account->opening_balance,
                    $data['opening_balance']
                )
            ) {
                throw ValidationException::withMessages([
                    'opening_balance' => [
                        'Opening balance cannot be changed directly. Use an opening balance journal instead.'
                    ],
                ]);
            }

            $newType =
                $data['type'] ??
                $account->type;

            $newParent =
                array_key_exists(
                    'parent_id',
                    $data
                )
                ? $data['parent_id']
                : $account->parent_id;

            if (
                array_key_exists(
                    'parent_id',
                    $data
                ) ||
                array_key_exists(
                    'type',
                    $data
                )
            ) {
                $this->validateParent(
                    $newParent,
                    $newType,
                    $account
                );
            }

            if (
                array_key_exists(
                    'type',
                    $data
                ) &&
                $data['type'] !== $account->type
            ) {
                if (
                    $account
                    ->postedEntries()
                    ->exists() ||
                    $account
                    ->children()
                    ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'type' => [
                            'Account type cannot be changed after the account has posted entries or child accounts.'
                        ],
                    ]);
                }
            }

            if (
                array_key_exists(
                    'parent_id',
                    $data
                ) &&
                $this->changed(
                    $account->parent_id,
                    $data['parent_id']
                ) &&
                $account
                ->postedEntries()
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'parent_id' => [
                        'Posted account cannot be moved to another parent.'
                    ],
                ]);
            }

            if (
                array_key_exists(
                    'parent_id',
                    $data
                ) &&
                $data['parent_id']
            ) {
                $parent = Account::query()
                    ->whereKey(
                        $data['parent_id']
                    )
                    ->lockForUpdate()
                    ->first();

                if (
                    $parent &&
                    $parent
                    ->postedEntries()
                    ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'parent_id' => [
                            'An account with posted journal entries cannot become a parent account.'
                        ],
                    ]);
                }
            }

            $update = [];

            foreach (
                [
                    'parent_id',
                    'code',
                    'name',
                    'type',
                    'sub_type',
                    'is_active',
                    'description',
                ] as $field
            ) {
                if (
                    array_key_exists(
                        $field,
                        $data
                    )
                ) {
                    $update[$field] =
                        $data[$field];
                }
            }

            if (
                array_key_exists(
                    'code',
                    $update
                )
            ) {
                $update['code'] = trim(
                    (string)$update['code']
                );
            }

            if (
                array_key_exists(
                    'name',
                    $update
                )
            ) {
                $update['name'] = trim(
                    (string)$update['name']
                );
            }

            foreach (
                [
                    'sub_type',
                    'description',
                ] as $field
            ) {
                if (
                    array_key_exists(
                        $field,
                        $update
                    )
                ) {
                    $update[$field] =
                        $this->nullableString(
                            $update[$field]
                        );
                }
            }

            if (
                array_key_exists(
                    'is_active',
                    $update
                )
            ) {
                $update['is_active'] =
                    (bool)$update['is_active'];
            }

            $account->update(
                $update
            );

            DB::afterCommit(
                fn() =>
                $this
                    ->financeDashboardService
                    ->forgetCache()
            );

            return $this->fresh(
                $account
            );
        });
    }

    public function toggle(
        Account $account
    ): Account {
        return DB::transaction(function () use (
            $account
        ) {
            $account = Account::query()
                ->whereKey($account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->approval_status === 'pending') {
                throw ValidationException::withMessages([
                    'account' => [
                        'Account is pending approval and cannot be toggled yet.'
                    ],
                ]);
            }

            if ($account->is_system) {
                throw ValidationException::withMessages([
                    'account' => [
                        'System accounts cannot be deactivated.'
                    ],
                ]);
            }

            if (
                $account->is_active &&
                $account
                ->children()
                ->where(
                    'is_active',
                    true
                )
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'account' => [
                        'Deactivate active child accounts first.'
                    ],
                ]);
            }

            if (
                !$account->is_active &&
                $account->parent_id
            ) {
                $parent = $account->parent;

                if (
                    !$parent ||
                    !$parent->is_active
                ) {
                    throw ValidationException::withMessages([
                        'account' => [
                            'Parent account must be active before this account can be activated.'
                        ],
                    ]);
                }
            }

            $account->update([
                'is_active' =>
                !$account->is_active,
            ]);

            DB::afterCommit(
                fn() =>
                $this
                    ->financeDashboardService
                    ->forgetCache()
            );

            return $this->fresh(
                $account
            );
        });
    }

    public function delete(
        Account $account
    ): void {
        DB::transaction(function () use (
            $account
        ) {
            $account = Account::query()
                ->whereKey(
                    $account->id
                )
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->is_system) {
                throw ValidationException::withMessages([
                    'account' => [
                        'System accounts cannot be deleted.'
                    ],
                ]);
            }

            if (
                $account
                ->children()
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'account' => [
                        'Account with child accounts cannot be deleted.'
                    ],
                ]);
            }

            /*
             * Keep raw entries() here.
             * Any journal history should block hard-delete,
             * including non-posted history.
             */
            if (
                $account
                ->entries()
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'account' => [
                        'Account with journal history cannot be deleted. Deactivate it instead.'
                    ],
                ]);
            }

            $account->delete();

            DB::afterCommit(
                fn() =>
                $this
                    ->financeDashboardService
                    ->forgetCache()
            );
        });
    }

    private function validateParent(
        ?int $parentId,
        string $type,
        ?Account $account = null
    ): void {
        if (!$parentId) {
            return;
        }

        $parent = Account::query()
            ->whereKey(
                $parentId
            )
            ->first();

        if (!$parent) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'Selected parent account does not exist.'
                ],
            ]);
        }

        if (!$parent->is_active) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'Parent account must be active.'
                ],
            ]);
        }

        if ($parent->type !== $type) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'Parent and child account must have the same account type.'
                ],
            ]);
        }

        if (
            $account &&
            (int)$parent->id ===
            (int)$account->id
        ) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'Account cannot be its own parent.'
                ],
            ]);
        }

        if (
            $account &&
            in_array(
                (int)$parent->id,
                $this->descendantIds(
                    $account
                ),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'A child account cannot be selected as parent.'
                ],
            ]);
        }
    }

    private function descendantIds(
        Account $account
    ): array {
        $ids = [];
        $pending = [
            (int)$account->id
        ];

        while ($pending) {
            $children = Account::query()
                ->whereIn(
                    'parent_id',
                    $pending
                )
                ->pluck('id')
                ->map(
                    fn($id) =>
                    (int)$id
                )
                ->all();

            if (!$children) {
                break;
            }

            $ids = array_merge(
                $ids,
                $children
            );

            $pending = $children;
        }

        return array_values(
            array_unique(
                $ids
            )
        );
    }

    private function fresh(
        Account $account
    ): Account {
        return $account
            ->fresh([
                'parent:id,code,name,type',
            ])
            ->loadCount([
                'children',
                'postedEntries',
            ]);
    }

    private function changed(
        mixed $current,
        mixed $new
    ): bool {
        if (
            is_numeric($current) &&
            is_numeric($new)
        ) {
            return round(
                (float)$current,
                2
            ) !== round(
                (float)$new,
                2
            );
        }

        return (string)($current ?? '') !==
            (string)($new ?? '');
    }

    private function nullableString(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim(
            (string)$value
        );

        return $value === ''
            ? null
            : $value;
    }

    public function finalizeApproval(
        Account $account,
        array $decisionData,
        int $approvedBy
    ): Account {
        return DB::transaction(function () use (
            $account
        ) {
            $account = Account::query()
                ->whereKey($account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->approval_status !== 'pending') {
                throw ValidationException::withMessages([
                    'account' => [
                        'Only accounts pending approval can be approved.'
                    ],
                ]);
            }

            $account->update([
                'approval_status' => 'approved',
                'is_active' => true,
            ]);

            DB::afterCommit(
                fn() =>
                $this
                    ->financeDashboardService
                    ->forgetCache()
            );

            return $this->fresh($account);
        });
    }

    public function finalizeRejection(
        Account $account,
        string $reason,
        ?int $rejectedBy
    ): Account {
        return DB::transaction(function () use (
            $account
        ) {
            $account = Account::query()
                ->whereKey($account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->approval_status !== 'pending') {
                throw ValidationException::withMessages([
                    'account' => [
                        'Only accounts pending approval can be rejected.'
                    ],
                ]);
            }

            $account->update([
                'approval_status' => 'rejected',
                'is_active' => false,
            ]);

            DB::afterCommit(
                fn() =>
                $this
                    ->financeDashboardService
                    ->forgetCache()
            );

            return $this->fresh($account);
        });
    }

    public function finalizeCancellation(
        Account $account
    ): Account {
        return DB::transaction(function () use (
            $account
        ) {
            $account = Account::query()
                ->whereKey($account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->approval_status === 'pending') {
                $account->update([
                    'approval_status' => 'cancelled',
                    'is_active' => false,
                ]);
            }

            return $this->fresh($account);
        });
    }
}
