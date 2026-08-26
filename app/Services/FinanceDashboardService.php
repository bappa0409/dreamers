<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Asset;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPayment;
use App\Models\Transaction;
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
                'recent_transactions'=>$this->recentTransactions(),
            ]
        );
    }

    public function forgetCache(): void
    {
        Cache::forget('finance:dashboard');
    }

    private function summary(): array
    {
        $now=now();
        $today=$now->toDateString();
        $monthStart=$now->copy()->startOfMonth()->toDateString();
        $monthEnd=$now->copy()->endOfMonth()->toDateString();

        $balances=$this->accountBalances($today);

        $cash=$this->sumSubtype($balances,'cash');
        $bank=$this->sumSubtype($balances,'bank');
        $receivable=$this->sumSubtype($balances,'receivable');
        $investments=$this->sumSubtype($balances,'investment');

        $monthly=$this->incomeExpense(
            $monthStart,
            $monthEnd
        );

        $subscriptionDue=(float)SubscriptionDue::query()
            ->whereIn('status',[
                'unpaid',
                'partial',
                'overdue',
            ])
            ->selectRaw(
                'COALESCE(SUM(GREATEST(amount-paid_amount,0)),0) total'
            )
            ->value('total');

        $subscriptionCollected=(float)SubscriptionPayment::query()
            ->where('status','verified')
            ->whereBetween('verified_at',[
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])
            ->sum('amount');

        $assetBookValue=(float)Asset::query()
            ->where('status','active')
            ->selectRaw(
                'COALESCE(SUM(GREATEST(purchase_cost-accumulated_depreciation,0)),0) total'
            )
            ->value('total');

        $postedTransactions=Transaction::query()
            ->where('status','posted')
            ->whereBetween(
                'transaction_date',
                [$monthStart,$monthEnd]
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
                $monthly['income']-$monthly['expense'],
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

            'posted_transactions'=>$postedTransactions,
        ];
    }

    private function accountBalances(string $asOf)
    {
        $movements=DB::table('transaction_entries as te')
            ->join(
                'transactions as t',
                't.id',
                '=',
                'te.transaction_id'
            )
            ->where('t.status','posted')
            ->where(
                't.transaction_date',
                '<=',
                $asOf
            )

            ->selectRaw('
                te.account_id,
                COALESCE(SUM(te.debit),0) total_debit,
                COALESCE(SUM(te.credit),0) total_credit
            ')
            ->groupBy('te.account_id');

        return Account::query()
            ->select([
                'accounts.id',
                'accounts.type',
                'accounts.sub_type',
                'accounts.opening_balance',
            ])
            ->leftJoinSub(
                $movements,
                'movements',
                fn($join)=>$join->on(
                    'movements.account_id',
                    '=',
                    'accounts.id'
                )
            )
            ->leftJoin(
                'accounts as child',
                'child.parent_id',
                '=',
                'accounts.id'
            )
            ->whereNull('child.id')
            ->selectRaw('COALESCE(movements.total_debit,0) total_debit')
            ->selectRaw('COALESCE(movements.total_credit,0) total_credit')
            ->get()
            ->map(function(Account $account){
                return[
                    'type'=>$account->type,
                    'sub_type'=>$account->sub_type,

                    'balance'=>round(
                        $account->calculateBalance(
                            (float)($account->total_debit??0),
                            (float)($account->total_credit??0)
                        ),
                        2
                    ),
                ];
            });
    }

    private function sumSubtype(
        $balances,
        string $subType
    ): float{
        return (float)$balances
            ->where('sub_type',$subType)
            ->sum('balance');
    }

    private function incomeExpense(
        string $from,
        string $to
    ): array{
        $row=DB::table('transaction_entries as te')
            ->join(
                'transactions as t',
                't.id',
                '=',
                'te.transaction_id'
            )
            ->join(
                'accounts as a',
                'a.id',
                '=',
                'te.account_id'
            )
            ->where('t.status','posted')
            ->whereBetween(
                't.transaction_date',
                [$from,$to]
            )
            ->whereIn(
                'a.type',
                ['income','expense']
            )
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN a.type='income'
                        THEN te.credit-te.debit
                        ELSE 0
                    END
                ),0) income,

                COALESCE(SUM(
                    CASE
                        WHEN a.type='expense'
                        THEN te.debit-te.credit
                        ELSE 0
                    END
                ),0) expense
            ")
            ->first();

        return[
            'income'=>(float)($row->income??0),
            'expense'=>(float)($row->expense??0),
        ];
    }

    private function monthlyTrend(): array
    {
        $months=collect(range(5,0))
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
                        ->toDateString(),
                ];
            });

        $rows=DB::table('transaction_entries as te')
            ->join(
                'transactions as t',
                't.id',
                '=',
                'te.transaction_id'
            )
            ->join(
                'accounts as a',
                'a.id',
                '=',
                'te.account_id'
            )
            ->where('t.status','posted')
            ->whereBetween(
                't.transaction_date',
                [
                    $months->first()['from'],
                    $months->last()['to'],
                ]
            )
            ->whereIn(
                'a.type',
                ['income','expense']
            )
            ->selectRaw("
                YEAR(t.transaction_date) year,
                MONTH(t.transaction_date) month,

                COALESCE(SUM(
                    CASE
                        WHEN a.type='income'
                        THEN te.credit-te.debit
                        ELSE 0
                    END
                ),0) income,

                COALESCE(SUM(
                    CASE
                        WHEN a.type='expense'
                        THEN te.debit-te.credit
                        ELSE 0
                    END
                ),0) expense
            ")
            ->groupByRaw(
                'YEAR(t.transaction_date),MONTH(t.transaction_date)'
            )
            ->get()
            ->keyBy(
                fn($row)=>sprintf(
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

                $income=(float)($row->income??0);
                $expense=(float)($row->expense??0);

                return[
                    'month'=>$month['key'],
                    'label'=>$month['label'],
                    'income'=>round($income,2),
                    'expense'=>round($expense,2),
                    'net'=>round(
                        $income-$expense,
                        2
                    ),
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
                'poster:id,name',
            ])
            ->withSum(
                'entries as total_debit',
                'debit'
            )
            ->latest('transaction_date')
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
                'posted_by',
            ]);
    }
}