<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Collection;

class ProfitLossService
{
    public function report(array $filters=[]): array
    {
        $from=$filters['from']??now()->startOfMonth()->toDateString();
        $to=$filters['to']??now()->endOfMonth()->toDateString();

        $accounts=$this->balances($from,$to);

        $income=$accounts
            ->where('type','income')
            ->values();

        $expenses=$accounts
            ->where('type','expense')
            ->values();

        $totalIncome=round(
            $income->sum('balance'),
            2
        );

        $totalExpense=round(
            $expenses->sum('balance'),
            2
        );

        $net=round(
            $totalIncome-$totalExpense,
            2
        );

        return[
            'from'=>$from,
            'to'=>$to,
            'income'=>$income,
            'expenses'=>$expenses,
            'summary'=>[
                'total_income'=>$totalIncome,
                'total_expense'=>$totalExpense,
                'net_surplus'=>$net,
                'is_surplus'=>$net>=0
            ]
        ];
    }

    private function balances(
        string $from,
        string $to
    ): Collection{
        return Account::query()
            ->select([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.sub_type',
                'accounts.is_active'
            ])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN transactions.id IS NOT NULL THEN transaction_entries.debit ELSE 0 END),0) AS total_debit'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN transactions.id IS NOT NULL THEN transaction_entries.credit ELSE 0 END),0) AS total_credit'
            )
            ->leftJoin(
                'transaction_entries',
                'transaction_entries.account_id',
                '=',
                'accounts.id'
            )
            ->leftJoin(
                'transactions',
                function($join)use($from,$to){
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
                    ->whereBetween(
                        'transactions.transaction_date',
                        [$from,$to]
                    );
                }
            )
            ->whereDoesntHave('children')
            ->whereIn(
                'accounts.type',
                ['income','expense']
            )
            ->groupBy([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.sub_type',
                'accounts.is_active'
            ])
            ->orderBy('accounts.code')
            ->get()
            ->map(function($account){
                $debit=(float)$account->total_debit;
                $credit=(float)$account->total_credit;

                $balance=$account->type==='income'
                    ?$credit-$debit
                    :$debit-$credit;

                return[
                    'id'=>$account->id,
                    'code'=>$account->code,
                    'name'=>$account->name,
                    'type'=>$account->type,
                    'sub_type'=>$account->sub_type,
                    'is_active'=>(bool)$account->is_active,
                    'balance'=>round($balance,2)
                ];
            })
            ->filter(
                fn($account)=>
                    abs($account['balance'])>0.004
            )
            ->values();
    }
}