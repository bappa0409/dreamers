<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class AccountingService
{
    public function post(array $data): Transaction
    {
        return DB::transaction(function()use($data){
            $entries=$data['entries']??[];

            if(count($entries)<2){
                throw ValidationException::withMessages([
                    'entries'=>[
                        'At least two journal entries are required.'
                    ]
                ]);
            }

            $debit=round(
                collect($entries)->sum(
                    fn($entry)=>(float)(
                        $entry['debit']??0
                    )
                ),
                2
            );

            $credit=round(
                collect($entries)->sum(
                    fn($entry)=>(float)(
                        $entry['credit']??0
                    )
                ),
                2
            );

            if(
                $debit<=0||
                $debit!==$credit
            ){
                throw ValidationException::withMessages([
                    'entries'=>[
                        'Total debit and credit must be equal and greater than zero.'
                    ]
                ]);
            }

            $accountIds=collect($entries)
                ->pluck('account_id')
                ->map(fn($id)=>(int)$id)
                ->unique()
                ->values();

            $accounts=Account::query()
                ->whereIn(
                    'id',
                    $accountIds
                )
                ->where('is_active',true)
                ->withCount('children')
                ->get()
                ->keyBy('id');

            if(
                $accounts->count()!==
                $accountIds->count()
            ){
                throw ValidationException::withMessages([
                    'entries'=>[
                        'One or more accounts are invalid or inactive.'
                    ]
                ]);
            }

            foreach($accountIds as $accountId){
                $account=$accounts->get(
                    $accountId
                );

                if($account->children_count>0){
                    throw ValidationException::withMessages([
                        'entries'=>[
                            "Account {$account->code} - {$account->name} is a parent account and cannot receive journal postings."
                        ]
                    ]);
                }
            }

            foreach($entries as $entry){
                $entryDebit=round(
                    (float)(
                        $entry['debit']??0
                    ),
                    2
                );

                $entryCredit=round(
                    (float)(
                        $entry['credit']??0
                    ),
                    2
                );

                if(
                    (
                        $entryDebit<=0&&
                        $entryCredit<=0
                    )||
                    (
                        $entryDebit>0&&
                        $entryCredit>0
                    )
                ){
                    throw ValidationException::withMessages([
                        'entries'=>[
                            'Each journal line must contain either a debit or credit amount.'
                        ]
                    ]);
                }
            }

            $userId=$data['user_id']
                ??auth()->id();

            $transaction=Transaction::create([
                'transaction_no'=>$this->generateNumber(),
                'transaction_date'=>$data['transaction_date']
                    ??now()->toDateString(),
                'type'=>$data['type']
                    ??'manual_journal',
                'source_module'=>$data['source_module']
                    ??'manual',
                'source_id'=>$data['source_id']
                    ??null,
                'reference_type'=>$data['reference_type']
                    ??null,
                'reference_id'=>$data['reference_id']
                    ??null,
                'description'=>$data['description']
                    ??null,
                'status'=>'posted',
                'created_by'=>$userId,
                'posted_at'=>now(),
                'posted_by'=>$userId
            ]);

            $transaction->entries()->createMany(
                collect($entries)
                    ->map(fn($entry)=>[
                        'account_id'=>$entry['account_id'],
                        'debit'=>round(
                            (float)(
                                $entry['debit']??0
                            ),
                            2
                        ),
                        'credit'=>round(
                            (float)(
                                $entry['credit']??0
                            ),
                            2
                        ),
                        'description'=>$entry['description']
                            ??null
                    ])
                    ->all()
            );

            Cache::forget('finance:dashboard');
            
            return $transaction->load(
                'entries.account'
            );
        });
    }

    public function account(string $subType): Account
    {
        $account=Account::query()
            ->where('sub_type',$subType)
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'account'=>[
                    "Active posting account for '{$subType}' was not found."
                ]
            ]);
        }

        return $account;
    }

    private function generateNumber(): string
    {
        $prefix='JV-'.
            now()->format('Ym').
            '-';

        $last=Transaction::query()
            ->where(
                'transaction_no',
                'like',
                $prefix.'%'
            )
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('transaction_no');

        $next=$last
            ?((int)substr(
                $last,
                -6
            ))+1
            :1;

        return $prefix.str_pad(
            (string)$next,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}