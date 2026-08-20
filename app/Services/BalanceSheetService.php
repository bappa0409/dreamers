<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Collection;

class BalanceSheetService
{
    public function report(array $filters=[]): array
    {
        $asOf=$filters['as_of']??now()->toDateString();
        $accounts=$this->balances($asOf);

        $assets=$accounts
            ->where('type','asset')
            ->values();

        $liabilities=$accounts
            ->where('type','liability')
            ->values();

        $equity=$accounts
            ->where('type','equity')
            ->values();

        $income=$accounts
            ->where('type','income')
            ->sum('balance');

        $expenses=$accounts
            ->where('type','expense')
            ->sum('balance');

        $currentSurplus=round(
            (float)$income-(float)$expenses,
            2
        );

        $totalAssets=round(
            $assets->sum('balance'),
            2
        );

        $totalLiabilities=round(
            $liabilities->sum('balance'),
            2
        );

        $baseEquity=round(
            $equity->sum('balance'),
            2
        );

        $totalEquity=round(
            $baseEquity+$currentSurplus,
            2
        );

        $liabilitiesAndEquity=round(
            $totalLiabilities+$totalEquity,
            2
        );

        $difference=round(
            $totalAssets-$liabilitiesAndEquity,
            2
        );

        return[
            'as_of'=>$asOf,
            'assets'=>$assets,
            'liabilities'=>$liabilities,
            'equity'=>$equity,
            'current_surplus'=>$currentSurplus,
            'summary'=>[
                'total_assets'=>$totalAssets,
                'total_liabilities'=>$totalLiabilities,
                'base_equity'=>$baseEquity,
                'current_surplus'=>$currentSurplus,
                'total_equity'=>$totalEquity,
                'liabilities_and_equity'=>$liabilitiesAndEquity,
                'difference'=>$difference,
                'is_balanced'=>abs($difference)<0.01
            ]
        ];
    }

    private function balances(string $asOf): Collection
    {
        return Account::query()
            ->select([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.sub_type',
                'accounts.opening_balance',
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
            ->whereDoesntHave('children')
            ->whereIn(
                'accounts.type',
                [
                    'asset',
                    'liability',
                    'equity',
                    'income',
                    'expense'
                ]
            )
            ->groupBy([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.sub_type',
                'accounts.opening_balance',
                'accounts.is_active'
            ])
            ->orderBy('accounts.code')
            ->get()
            ->map(
                fn($account)=>
                    $this->transform($account)
            )
            ->filter(
                fn($account)=>
                    abs($account['balance'])>0.004
            )
            ->values();
    }

    private function transform(Account $account): array
    {
        $opening=(float)$account->opening_balance;
        $debit=(float)$account->total_debit;
        $credit=(float)$account->total_credit;

        $balance=in_array(
            $account->type,
            ['asset','expense'],
            true
        )
            ?$opening+$debit-$credit
            :$opening+$credit-$debit;

        return[
            'id'=>$account->id,
            'code'=>$account->code,
            'name'=>$account->name,
            'type'=>$account->type,
            'sub_type'=>$account->sub_type,
            'is_active'=>(bool)$account->is_active,
            'balance'=>round($balance,2)
        ];
    }
}