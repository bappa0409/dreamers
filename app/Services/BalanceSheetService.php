<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BalanceSheetService
{
    public function report(array $filters=[]): array
    {
        $asOf=$filters['as_of']??now()->toDateString();
        $accounts=$this->balances($asOf);

        $assets=$accounts->where('type','asset')->values();
        $liabilities=$accounts->where('type','liability')->values();
        $equity=$accounts->where('type','equity')->values();

        $income=app_round((float)$accounts->where('type','income')->sum('balance'),2);
        $expenses=app_round((float)$accounts->where('type','expense')->sum('balance'),2);
        $currentSurplus=app_round($income-$expenses,2);

        $totalAssets=app_round((float)$assets->sum('balance'),2);
        $totalLiabilities=app_round((float)$liabilities->sum('balance'),2);
        $baseEquity=app_round((float)$equity->sum('balance'),2);
        $totalEquity=app_round($baseEquity+$currentSurplus,2);
        $liabilitiesAndEquity=app_round($totalLiabilities+$totalEquity,2);
        $difference=app_round($totalAssets-$liabilitiesAndEquity,2);

        return[
            'as_of'=>$asOf,
            'assets'=>$assets,
            'liabilities'=>$liabilities,
            'equity'=>$equity,
            'current_period'=>[
                'income'=>$income,
                'expense'=>$expenses,
                'surplus'=>$currentSurplus,
            ],
            'summary'=>[
                'total_assets'=>$totalAssets,
                'total_liabilities'=>$totalLiabilities,
                'base_equity'=>$baseEquity,
                'current_surplus'=>$currentSurplus,
                'total_equity'=>$totalEquity,
                'liabilities_and_equity'=>$liabilitiesAndEquity,
                'difference'=>$difference,
                'is_balanced'=>abs($difference)<0.01,
            ],
        ];
    }

    private function balances(string $asOf): Collection
    {
        $movements=DB::table('transaction_entries as te')
            ->join('transactions as t','t.id','=','te.transaction_id')
            ->where('t.status','posted')
            ->whereDate('t.transaction_date','<=',$asOf)
            ->selectRaw('
                te.account_id,
                COALESCE(SUM(te.debit),0) total_debit,
                COALESCE(SUM(te.credit),0) total_credit
            ')
            ->groupBy('te.account_id');

        return Account::query()
            ->select([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.sub_type',
                'accounts.opening_balance',
                'accounts.is_active',
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
            ->selectRaw(
                'COALESCE(movements.total_debit,0) AS total_debit'
            )
            ->selectRaw(
                'COALESCE(movements.total_credit,0) AS total_credit'
            )
            ->whereDoesntHave('children')
            ->whereIn('accounts.type',[
                'asset',
                'liability',
                'equity',
                'income',
                'expense',
            ])
            ->orderBy('accounts.code')
            ->get()
            ->map(fn(Account $account)=>$this->transform($account))
            ->filter(fn(array $account)=>abs($account['balance'])>0.004)
            ->values();
    }

    private function transform(Account $account): array
    {
        $balance=$account->calculateBalance(
            (float)($account->total_debit??0),
            (float)($account->total_credit??0)
        );

        return[
            'id'=>$account->id,
            'code'=>$account->code,
            'name'=>$account->name,
            'type'=>$account->type,
            'sub_type'=>$account->sub_type,
            'is_active'=>(bool)$account->is_active,
            'balance'=>app_round($balance,2),
        ];
    }
}