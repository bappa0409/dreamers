<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProfitLossService
{
    public function report(array $filters=[]): array
    {
        $from=$filters['from']??now()->startOfMonth()->toDateString();
        $to=$filters['to']??now()->endOfMonth()->toDateString();

        $accounts=$this->balances($from,$to);
        $income=$accounts->where('type','income')->values();
        $expenses=$accounts->where('type','expense')->values();

        $totalIncome=app_round((float)$income->sum('balance'),2);
        $totalExpense=app_round((float)$expenses->sum('balance'),2);
        $net=app_round($totalIncome-$totalExpense,2);

        return[
            'from'=>$from,
            'to'=>$to,
            'income'=>$income,
            'expenses'=>$expenses,
            'summary'=>[
                'total_income'=>$totalIncome,
                'total_expense'=>$totalExpense,
                'net_surplus'=>$net,
                'net_deficit'=>$net<0?abs($net):0,
                'is_surplus'=>$net>=0,
            ],
        ];
    }

    private function balances(string $from,string $to): Collection
    {
        $movements=DB::table('transaction_entries as te')
            ->join(
                'transactions as t',
                't.id',
                '=',
                'te.transaction_id'
            )
            ->where('t.status','posted')
            ->whereBetween(
                't.transaction_date',
                [$from,$to]
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
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.sub_type',
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
            ->whereIn(
                'accounts.type',
                ['income','expense']
            )
            ->orderBy('accounts.code')
            ->get()
            ->map(fn(Account $account)=>$this->transform($account))
            ->filter(
                fn(array $account)=>
                    abs($account['balance'])>0.004
            )
            ->values();
    }

    private function transform(Account $account): array
    {
        $debit=(float)($account->total_debit??0);
        $credit=(float)($account->total_credit??0);

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
            'debit'=>app_round($debit,2),
            'credit'=>app_round($credit,2),
            'balance'=>app_round($balance,2),
        ];
    }
}