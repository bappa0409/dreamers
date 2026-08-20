<?php

namespace App\Services;

use App\Models\Account;
use App\Models\TransactionEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class GeneralLedgerService
{
    public function accounts()
    {
        return Account::query()
            ->whereDoesntHave('children')
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
                'type',
                'sub_type',
                'is_active'
            ]);
    }

    public function ledger(
        Account $account,
        array $filters=[]
    ): array{
        if($account->children()->exists()){
            throw ValidationException::withMessages([
                'account'=>[
                    'General Ledger can only be viewed for posting/leaf accounts.'
                ]
            ]);
        }

        $from=$filters['from']??null;
        $to=$filters['to']??null;
        $perPage=min((int)($filters['per_page']??25),100);

        $openingBalance=$this->openingBalance(
            $account,
            $from
        );

        $periodBase=$this->periodQuery(
            $account,
            $from,
            $to
        );

        $periodDebit=(float)(clone $periodBase)->sum(
            'transaction_entries.debit'
        );

        $periodCredit=(float)(clone $periodBase)->sum(
            'transaction_entries.credit'
        );

        $closingBalance=$this->applyMovement(
            $account,
            $openingBalance,
            $periodDebit,
            $periodCredit
        );

        $entries=$this->periodQuery(
            $account,
            $from,
            $to
        )
            ->with([
                'transaction:id,transaction_no,transaction_date,type,source_module,source_id,description,status,created_by,posted_by',
                'transaction.creator:id,name',
                'transaction.poster:id,name'
            ])
            ->orderBy(
                'transactions.transaction_date'
            )
            ->orderBy(
                'transaction_entries.transaction_id'
            )
            ->orderBy(
                'transaction_entries.id'
            )
            ->paginate($perPage)
            ->withQueryString();

        $this->attachRunningBalances(
            $account,
            $entries,
            $openingBalance,
            $from,
            $to
        );

        return[
            'account'=>[
                'id'=>$account->id,
                'code'=>$account->code,
                'name'=>$account->name,
                'type'=>$account->type,
                'sub_type'=>$account->sub_type,
                'is_active'=>$account->is_active
            ],
            'summary'=>[
                'opening_balance'=>round($openingBalance,2),
                'period_debit'=>round($periodDebit,2),
                'period_credit'=>round($periodCredit,2),
                'closing_balance'=>round($closingBalance,2)
            ],
            'entries'=>$entries
        ];
    }

    private function periodQuery(
        Account $account,
        ?string $from,
        ?string $to
    ): Builder{
        return TransactionEntry::query()
            ->select('transaction_entries.*')
            ->join(
                'transactions',
                'transactions.id',
                '=',
                'transaction_entries.transaction_id'
            )
            ->where(
                'transaction_entries.account_id',
                $account->id
            )
            ->where(
                'transactions.status',
                'posted'
            )
            ->when(
                $from,
                fn($query)=>
                    $query->whereDate(
                        'transactions.transaction_date',
                        '>=',
                        $from
                    )
            )
            ->when(
                $to,
                fn($query)=>
                    $query->whereDate(
                        'transactions.transaction_date',
                        '<=',
                        $to
                    )
            );
    }

    private function openingBalance(
        Account $account,
        ?string $from
    ): float{
        $opening=(float)$account->opening_balance;

        if(!$from){
            return $opening;
        }

        $before=TransactionEntry::query()
            ->select('transaction_entries.*')
            ->join(
                'transactions',
                'transactions.id',
                '=',
                'transaction_entries.transaction_id'
            )
            ->where(
                'transaction_entries.account_id',
                $account->id
            )
            ->where(
                'transactions.status',
                'posted'
            )
            ->whereDate(
                'transactions.transaction_date',
                '<',
                $from
            );

        return $this->applyMovement(
            $account,
            $opening,
            (float)(clone $before)->sum(
                'transaction_entries.debit'
            ),
            (float)(clone $before)->sum(
                'transaction_entries.credit'
            )
        );
    }

    private function attachRunningBalances(
        Account $account,
        LengthAwarePaginator $entries,
        float $openingBalance,
        ?string $from,
        ?string $to
    ): void{
        $items=$entries->getCollection();

        if($items->isEmpty()){
            return;
        }

        $first=$items->first();

        $beforePage=$this->periodQuery(
            $account,
            $from,
            $to
        )->where(function($query)use($first){
            $query
                ->where(
                    'transactions.transaction_date',
                    '<',
                    $first->transaction->transaction_date
                )
                ->orWhere(function($q)use($first){
                    $q->where(
                        'transactions.transaction_date',
                        '=',
                        $first->transaction->transaction_date
                    )->where(
                        'transaction_entries.transaction_id',
                        '<',
                        $first->transaction_id
                    );
                })
                ->orWhere(function($q)use($first){
                    $q->where(
                        'transactions.transaction_date',
                        '=',
                        $first->transaction->transaction_date
                    )->where(
                        'transaction_entries.transaction_id',
                        '=',
                        $first->transaction_id
                    )->where(
                        'transaction_entries.id',
                        '<',
                        $first->id
                    );
                });
        });

        $running=$this->applyMovement(
            $account,
            $openingBalance,
            (float)(clone $beforePage)->sum(
                'transaction_entries.debit'
            ),
            (float)(clone $beforePage)->sum(
                'transaction_entries.credit'
            )
        );

        $items->transform(function($entry)use(
            $account,
            &$running
        ){
            $running=$this->applyMovement(
                $account,
                $running,
                (float)$entry->debit,
                (float)$entry->credit
            );

            $entry->running_balance=round(
                $running,
                2
            );

            return $entry;
        });

        $entries->setCollection($items);
    }

    private function applyMovement(
        Account $account,
        float $balance,
        float $debit,
        float $credit
    ): float{
        if(
            in_array(
                $account->type,
                ['asset','expense'],
                true
            )
        ){
            return $balance+$debit-$credit;
        }

        return $balance+$credit-$debit;
    }
}