<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    public function report(array $filters=[]): array
    {
        $asOf=$filters['as_of']??now()->toDateString();
        $search=trim((string)($filters['search']??''));
        $type=$filters['type']??null;
        $showZero=filter_var(
            $filters['show_zero']??false,
            FILTER_VALIDATE_BOOLEAN
        );

        $perPage=min(
            max((int)($filters['per_page']??25),5),
            100
        );

        $page=max(
            (int)($filters['page']??1),
            1
        );

        /*
        |--------------------------------------------------------------------------
        | All accounts
        |--------------------------------------------------------------------------
        |
        | Summary MUST be calculated before search/type filters.
        | Otherwise filtered Trial Balance can incorrectly appear unbalanced.
        |
        */

        $allRows=$this->balances($asOf);

        $summaryRows=$allRows
            ->filter(fn(array $row)=>
                abs($row['debit_balance'])>0.004||
                abs($row['credit_balance'])>0.004
            )
            ->values();

        $totalDebit=round(
            (float)$summaryRows->sum('debit_balance'),
            2
        );

        $totalCredit=round(
            (float)$summaryRows->sum('credit_balance'),
            2
        );

        $difference=round(
            $totalDebit-$totalCredit,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Display filters
        |--------------------------------------------------------------------------
        */

        $rows=$allRows;

        if($type){
            $rows=$rows
                ->where('type',$type)
                ->values();
        }

        if($search!==''){
            $needle=mb_strtolower($search);

            $rows=$rows
                ->filter(function(array $row)use($needle){
                    return str_contains(
                        mb_strtolower((string)$row['code']),
                        $needle
                    )||
                    str_contains(
                        mb_strtolower((string)$row['name']),
                        $needle
                    )||
                    str_contains(
                        mb_strtolower((string)($row['sub_type']??'')),
                        $needle
                    );
                })
                ->values();
        }

        if(!$showZero){
            $rows=$rows
                ->filter(fn(array $row)=>
                    abs($row['debit_balance'])>0.004||
                    abs($row['credit_balance'])>0.004
                )
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Filtered totals
        |--------------------------------------------------------------------------
        |
        | Useful for UI while overall Trial Balance remains independently checked.
        |
        */

        $filteredDebit=round(
            (float)$rows->sum('debit_balance'),
            2
        );

        $filteredCredit=round(
            (float)$rows->sum('credit_balance'),
            2
        );

        return[
            'as_of'=>$asOf,

            'summary'=>[
                'total_accounts'=>$summaryRows->count(),
                'total_debit'=>$totalDebit,
                'total_credit'=>$totalCredit,
                'difference'=>$difference,
                'is_balanced'=>abs($difference)<0.01,
            ],

            'filtered_summary'=>[
                'total_accounts'=>$rows->count(),
                'total_debit'=>$filteredDebit,
                'total_credit'=>$filteredCredit,
            ],

            'accounts'=>$this->paginate(
                $rows,
                $perPage,
                $page
            ),
        ];
    }

    private function balances(string $asOf): Collection
    {
        /*
        |--------------------------------------------------------------------------
        | Aggregate posted movements first
        |--------------------------------------------------------------------------
        */

        $movements=DB::table('transaction_entries as te')
            ->join(
                'transactions as t',
                't.id',
                '=',
                'te.transaction_id'
            )
            ->where(
                't.status',
                'posted'
            )
            ->whereDate(
                't.transaction_date',
                '<=',
                $asOf
            )
            ->selectRaw('
                te.account_id,
                COALESCE(SUM(te.debit),0) total_debit,
                COALESCE(SUM(te.credit),0) total_credit
            ')
            ->groupBy(
                'te.account_id'
            );

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
            ->whereDoesntHave(
                'children'
            )
            ->whereIn(
                'accounts.type',
                [
                    'asset',
                    'liability',
                    'equity',
                    'income',
                    'expense',
                ]
            )
            ->orderBy(
                'accounts.code'
            )
            ->get()
            ->map(
                fn(Account $account)=>
                    $this->transformAccount(
                        $account
                    )
            )
            ->values();
    }

    private function transformAccount(
        Account $account
    ): array{
        $opening=(float)$account->opening_balance;
        $debit=(float)($account->total_debit??0);
        $credit=(float)($account->total_credit??0);

        $balance=$account->calculateBalance(
            $debit,
            $credit
        );

        $debitNormal=in_array(
            $account->type,
            ['asset','expense'],
            true
        );

        $debitBalance=0.0;
        $creditBalance=0.0;

        if($debitNormal){
            $balance>=0
                ?$debitBalance=$balance
                :$creditBalance=abs($balance);
        }else{
            $balance>=0
                ?$creditBalance=$balance
                :$debitBalance=abs($balance);
        }

        return[
            'id'=>$account->id,
            'code'=>$account->code,
            'name'=>$account->name,
            'type'=>$account->type,
            'sub_type'=>$account->sub_type,
            'is_active'=>(bool)$account->is_active,

            'opening_balance'=>round(
                $opening,
                2
            ),

            /*
            |--------------------------------------------------------------------------
            | Kept for frontend backward compatibility
            |--------------------------------------------------------------------------
            |
            | Technically these are movements up to as_of, not a bounded "period".
            |
            */

            'period_debit'=>round(
                $debit,
                2
            ),

            'period_credit'=>round(
                $credit,
                2
            ),

            'debit_balance'=>round(
                $debitBalance,
                2
            ),

            'credit_balance'=>round(
                $creditBalance,
                2
            ),
        ];
    }

    private function paginate(
        Collection $items,
        int $perPage,
        int $page
    ): LengthAwarePaginator{
        return new LengthAwarePaginator(
            $items
                ->forPage(
                    $page,
                    $perPage
                )
                ->values(),

            $items->count(),

            $perPage,

            $page,

            [
                'path'=>request()->url(),
                'query'=>request()->query(),
            ]
        );
    }
}