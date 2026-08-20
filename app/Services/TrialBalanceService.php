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

        $rows=Account::query()
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
            ->when(
                $type,
                fn($query,$type)=>
                    $query->where(
                        'accounts.type',
                        $type
                    )
            )
            ->when(
                $search,
                function($query,$search){
                    $query->where(function($q)use($search){
                        $q->where(
                            'accounts.code',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'accounts.name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'accounts.sub_type',
                            'like',
                            "%{$search}%"
                        );
                    });
                }
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
                    $this->transformAccount(
                        $account
                    )
            );

        if(!$showZero){
            $rows=$rows
                ->filter(
                    fn($row)=>
                        abs($row['debit_balance'])>0.004||
                        abs($row['credit_balance'])>0.004
                )
                ->values();
        }

        $totalDebit=round(
            $rows->sum('debit_balance'),
            2
        );

        $totalCredit=round(
            $rows->sum('credit_balance'),
            2
        );

        $difference=round(
            $totalDebit-$totalCredit,
            2
        );

        $paginator=$this->paginate(
            $rows,
            $perPage,
            $page
        );

        return[
            'as_of'=>$asOf,
            'summary'=>[
                'total_accounts'=>$rows->count(),
                'total_debit'=>$totalDebit,
                'total_credit'=>$totalCredit,
                'difference'=>$difference,
                'is_balanced'=>abs($difference)<0.01
            ],
            'accounts'=>$paginator
        ];
    }

    private function transformAccount(
        Account $account
    ): array{
        $opening=(float)$account->opening_balance;
        $debit=(float)$account->total_debit;
        $credit=(float)$account->total_credit;

        $debitNormal=in_array(
            $account->type,
            ['asset','expense'],
            true
        );

        $balance=$debitNormal
            ?$opening+$debit-$credit
            :$opening+$credit-$debit;

        $debitBalance=0;
        $creditBalance=0;

        if($debitNormal){
            if($balance>=0){
                $debitBalance=$balance;
            }else{
                $creditBalance=abs(
                    $balance
                );
            }
        }else{
            if($balance>=0){
                $creditBalance=$balance;
            }else{
                $debitBalance=abs(
                    $balance
                );
            }
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
            )
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
                'query'=>request()->query()
            ]
        );
    }
}