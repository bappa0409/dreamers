<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPayment;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinanceDashboardService
{
    public function dashboard(): array
    {
        return Cache::remember(
            'finance:dashboard',
            now()->addMinutes(3),
            fn()=>[
                'summary'=>$this->summary(),
                'monthly_trend'=>$this->monthlyTrend(),
                'recent_transactions'=>$this->recentTransactions()
            ]
        );
    }

    public function forgetCache(): void
    {
        Cache::forget('finance:dashboard');
    }

    private function summary(): array
    {
        $today=now()->toDateString();
        $monthStart=now()->startOfMonth()->toDateString();
        $monthEnd=now()->endOfMonth()->toDateString();

        $balances=$this->accountBalances($today);

        $cash=$this->sumSubtype(
            $balances,
            'cash'
        );

        $bank=$this->sumSubtype(
            $balances,
            'bank'
        );

        $receivable=$this->sumSubtype(
            $balances,
            'receivable'
        );

        $investments=$this->sumSubtype(
            $balances,
            'investment'
        );

        $monthly=$this->incomeExpense(
            $monthStart,
            $monthEnd
        );

        $subscriptionDue=(float)SubscriptionDue::query()
            ->whereIn(
                'status',
                [
                    'unpaid',
                    'partial'
                ]
            )
            ->selectRaw(
                'COALESCE(SUM(amount-paid_amount),0) total'
            )
            ->value('total');

        $subscriptionCollected=(float)SubscriptionPayment::query()
            ->where('status','verified')
            ->whereBetween(
                'verified_at',
                [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]
            )
            ->sum('amount');

        $assetBookValue=(float)Asset::query()
            ->where('status','active')
            ->selectRaw(
                'COALESCE(SUM(purchase_cost-accumulated_depreciation),0) total'
            )
            ->value('total');

        $postedTransactions=Transaction::query()
            ->where('status','posted')
            ->whereBetween(
                'transaction_date',
                [
                    $monthStart,
                    $monthEnd
                ]
            )
            ->count();

        return[
            'cash'=>round($cash,2),
            'bank'=>round($bank,2),
            'cash_bank'=>round($cash+$bank,2),
            'receivable'=>round($receivable,2),
            'subscription_outstanding'=>round(
                $subscriptionDue,
                2
            ),
            'subscription_collected'=>round(
                $subscriptionCollected,
                2
            ),
            'monthly_income'=>round(
                $monthly['income'],
                2
            ),
            'monthly_expense'=>round(
                $monthly['expense'],
                2
            ),
            'net_surplus'=>round(
                $monthly['income']-
                $monthly['expense'],
                2
            ),
            'asset_book_value'=>round(
                $assetBookValue,
                2
            ),
            'investment_balance'=>round(
                $investments,
                2
            ),
            'posted_transactions'=>$postedTransactions
        ];
    }

    private function accountBalances(
        string $asOf
    ){
        return DB::table('accounts')
            ->leftJoin(
                'transaction_entries',
                'transaction_entries.account_id',
                '=',
                'accounts.id'
            )
            ->leftJoin(
                'transactions',
                function($join)use($asOf){
                    $join->on(
                        'transactions.id',
                        '=',
                        'transaction_entries.transaction_id'
                    )
                    ->where(
                        'transactions.status',
                        '=',
                        'posted'
                    )
                    ->whereDate(
                        'transactions.transaction_date',
                        '<=',
                        $asOf
                    );
                }
            )
            ->whereNotExists(function($query){
                $query->selectRaw('1')
                    ->from('accounts as children')
                    ->whereColumn(
                        'children.parent_id',
                        'accounts.id'
                    );
            })
            ->select([
                'accounts.id',
                'accounts.type',
                'accounts.sub_type',
                'accounts.opening_balance'
            ])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN transactions.id IS NOT NULL THEN transaction_entries.debit ELSE 0 END),0) total_debit'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN transactions.id IS NOT NULL THEN transaction_entries.credit ELSE 0 END),0) total_credit'
            )
            ->groupBy([
                'accounts.id',
                'accounts.type',
                'accounts.sub_type',
                'accounts.opening_balance'
            ])
            ->get()
            ->map(function($account){
                $opening=(float)$account->opening_balance;
                $debit=(float)$account->total_debit;
                $credit=(float)$account->total_credit;

                $balance=in_array(
                    $account->type,
                    [
                        'asset',
                        'expense'
                    ],
                    true
                )
                    ?$opening+$debit-$credit
                    :$opening+$credit-$debit;

                return[
                    'type'=>$account->type,
                    'sub_type'=>$account->sub_type,
                    'balance'=>round($balance,2)
                ];
            });
    }

    private function sumSubtype(
        $balances,
        string $subType
    ): float{
        return (float)$balances
            ->where(
                'sub_type',
                $subType
            )
            ->sum('balance');
    }

    private function incomeExpense(
        string $from,
        string $to
    ): array{
        $rows=DB::table('transaction_entries')
            ->join(
                'transactions',
                'transactions.id',
                '=',
                'transaction_entries.transaction_id'
            )
            ->join(
                'accounts',
                'accounts.id',
                '=',
                'transaction_entries.account_id'
            )
            ->where(
                'transactions.status',
                'posted'
            )
            ->whereBetween(
                'transactions.transaction_date',
                [
                    $from,
                    $to
                ]
            )
            ->whereIn(
                'accounts.type',
                [
                    'income',
                    'expense'
                ]
            )
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN accounts.type='income'
                        THEN transaction_entries.credit-transaction_entries.debit
                        ELSE 0
                    END
                ),0) income,
                COALESCE(SUM(
                    CASE
                        WHEN accounts.type='expense'
                        THEN transaction_entries.debit-transaction_entries.credit
                        ELSE 0
                    END
                ),0) expense
            ")
            ->first();

        return[
            'income'=>(float)($rows->income??0),
            'expense'=>(float)($rows->expense??0)
        ];
    }

    private function monthlyTrend(): array
    {
        $months=collect(
            range(5,0)
        )
            ->map(function($offset){
                $month=now()
                    ->copy()
                    ->subMonths($offset);

                return[
                    'key'=>$month->format('Y-m'),
                    'label'=>$month->format('M Y'),
                    'from'=>$month
                        ->copy()
                        ->startOfMonth()
                        ->toDateString(),
                    'to'=>$month
                        ->copy()
                        ->endOfMonth()
                        ->toDateString()
                ];
            });

        $from=$months
            ->first()['from'];

        $to=$months
            ->last()['to'];

        $rows=DB::table('transaction_entries')
            ->join(
                'transactions',
                'transactions.id',
                '=',
                'transaction_entries.transaction_id'
            )
            ->join(
                'accounts',
                'accounts.id',
                '=',
                'transaction_entries.account_id'
            )
            ->where(
                'transactions.status',
                'posted'
            )
            ->whereBetween(
                'transactions.transaction_date',
                [
                    $from,
                    $to
                ]
            )
            ->whereIn(
                'accounts.type',
                [
                    'income',
                    'expense'
                ]
            )
            ->selectRaw("
                YEAR(transactions.transaction_date) year,
                MONTH(transactions.transaction_date) month,
                COALESCE(SUM(
                    CASE
                        WHEN accounts.type='income'
                        THEN transaction_entries.credit-transaction_entries.debit
                        ELSE 0
                    END
                ),0) income,
                COALESCE(SUM(
                    CASE
                        WHEN accounts.type='expense'
                        THEN transaction_entries.debit-transaction_entries.credit
                        ELSE 0
                    END
                ),0) expense
            ")
            ->groupByRaw(
                'YEAR(transactions.transaction_date), MONTH(transactions.transaction_date)'
            )
            ->get()
            ->keyBy(
                fn($row)=>
                    sprintf(
                        '%04d-%02d',
                        $row->year,
                        $row->month
                    )
            );

        return $months
            ->map(function($month)use($rows){
                $row=$rows->get(
                    $month['key']
                );

                $income=(float)(
                    $row->income??0
                );

                $expense=(float)(
                    $row->expense??0
                );

                return[
                    'month'=>$month['key'],
                    'label'=>$month['label'],
                    'income'=>round(
                        $income,
                        2
                    ),
                    'expense'=>round(
                        $expense,
                        2
                    ),
                    'net'=>round(
                        $income-$expense,
                        2
                    )
                ];
            })
            ->values()
            ->all();
    }

    private function recentTransactions()
    {
        return Transaction::query()
            ->where('status','posted')
            ->with([
                'entries.account:id,code,name',
                'poster:id,name'
            ])
            ->withSum(
                'entries as total_debit',
                'debit'
            )
            ->latest(
                'transaction_date'
            )
            ->latest('id')
            ->limit(8)
            ->get([
                'id',
                'transaction_no',
                'transaction_date',
                'type',
                'source_module',
                'description',
                'status',
                'posted_by'
            ]);
    }
}