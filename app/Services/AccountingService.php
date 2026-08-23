<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected FinanceDashboardService $financeDashboardService,
        protected MemberDashboardService $memberDashboardService
    ){}

    public function post(array $data): Transaction
    {
        $idempotencyKey=isset($data['idempotency_key'])
            ?trim((string)$data['idempotency_key'])
            :null;

        $idempotencyKey=$idempotencyKey!==''?$idempotencyKey:null;

        try{
            return DB::transaction(function()use(
                $data,
                $idempotencyKey
            ){
                if($idempotencyKey){
                    $existing=$this->findByIdempotencyKey(
                        $idempotencyKey
                    );

                    if($existing){
                        return $existing;
                    }
                }

                $entries=$this->normalizeEntries(
                    $data['entries']??[]
                );

                $this->validateAccounts(
                    $entries
                );

                $userId=$data['user_id']
                    ??auth()->id();

                $transaction=Transaction::create([
                    'transaction_no'=>$this->generateNumber(),
                    'idempotency_key'=>$idempotencyKey,

                    'transaction_date'=>
                        $data['transaction_date']
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
                    'posted_by'=>$userId,
                ]);

                $transaction->entries()->createMany(
                    $entries
                );

                DB::afterCommit(function(){
                    $this->financeDashboardService
                        ->forgetCache();

                    $this->memberDashboardService
                        ->forgetFinancialCache();
                });

                return $transaction->load(
                    'entries.account'
                );
            });

        }catch(QueryException $exception){
            /*
            |--------------------------------------------------------------------------
            | Concurrent idempotency protection
            |--------------------------------------------------------------------------
            |
            | Two requests may both pass the initial SELECT.
            | DB unique index allows only one INSERT.
            | The loser returns the already-created transaction.
            |
            */

            if($idempotencyKey){
                $existing=$this->findByIdempotencyKey(
                    $idempotencyKey
                );

                if($existing){
                    return $existing;
                }
            }

            throw $exception;
        }
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
                ],
            ]);
        }

        return $account;
    }

    private function normalizeEntries(array $entries): array
    {
        if(count($entries)<2){
            throw ValidationException::withMessages([
                'entries'=>[
                    'At least two journal entries are required.'
                ],
            ]);
        }

        if(count($entries)>100){
            throw ValidationException::withMessages([
                'entries'=>[
                    'A journal cannot contain more than 100 lines.'
                ],
            ]);
        }

        $normalized=collect($entries)
            ->map(function($entry,$index){
                $accountId=(int)($entry['account_id']??0);

                $debit=round(
                    (float)($entry['debit']??0),
                    2
                );

                $credit=round(
                    (float)($entry['credit']??0),
                    2
                );

                if($accountId<=0){
                    throw ValidationException::withMessages([
                        "entries.{$index}.account_id"=>[
                            'A valid account is required.'
                        ],
                    ]);
                }

                if(
                    ($debit<=0&&$credit<=0)||
                    ($debit>0&&$credit>0)
                ){
                    throw ValidationException::withMessages([
                        "entries.{$index}"=>[
                            'Each journal line must contain either a debit or credit amount.'
                        ],
                    ]);
                }

                return[
                    'account_id'=>$accountId,
                    'debit'=>$debit,
                    'credit'=>$credit,

                    'description'=>isset(
                        $entry['description']
                    )
                        ?trim(
                            (string)$entry['description']
                        )
                        :null,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Compare in cents
        |--------------------------------------------------------------------------
        |
        | Avoid floating-point equality problems.
        |
        */

        $debitCents=$normalized->sum(
            fn($entry)=>
                (int)round(
                    $entry['debit']*100
                )
        );

        $creditCents=$normalized->sum(
            fn($entry)=>
                (int)round(
                    $entry['credit']*100
                )
        );

        if(
            $debitCents<=0||
            $debitCents!==$creditCents
        ){
            throw ValidationException::withMessages([
                'entries'=>[
                    'Total debit and credit must be equal and greater than zero.'
                ],
            ]);
        }

        return $normalized->all();
    }

    private function validateAccounts(array $entries): void
    {
        $accountIds=collect($entries)
            ->pluck('account_id')
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Lock posting accounts
        |--------------------------------------------------------------------------
        |
        | Prevent an account being deactivated or converted to parent while
        | a journal is being posted.
        |
        */

        $accounts=Account::query()
            ->whereIn('id',$accountIds)
            ->lockForUpdate()
            ->withCount('children')
            ->get()
            ->keyBy('id');

        if(
            $accounts->count()!==
            $accountIds->count()
        ){
            throw ValidationException::withMessages([
                'entries'=>[
                    'One or more selected accounts do not exist.'
                ],
            ]);
        }

        foreach($accountIds as $accountId){
            $account=$accounts->get(
                $accountId
            );

            if(!$account->is_active){
                throw ValidationException::withMessages([
                    'entries'=>[
                        "Account {$account->code} - {$account->name} is inactive."
                    ],
                ]);
            }

            if($account->children_count>0){
                throw ValidationException::withMessages([
                    'entries'=>[
                        "Account {$account->code} - {$account->name} is a parent account and cannot receive journal postings."
                    ],
                ]);
            }
        }
    }

    private function findByIdempotencyKey(
        string $key
    ): ?Transaction{
        return Transaction::query()
            ->where(
                'idempotency_key',
                $key
            )
            ->with(
                'entries.account'
            )
            ->first();
    }

    private function generateNumber(): string
    {
        $month=now()->format('Ym');
        $prefix="JV-{$month}-";

        return $this->numberSequenceService->next(
            key:"journal:{$month}",
            prefix:$prefix,
            digits:6,
            initialValue:function()use($prefix){
                $last=Transaction::query()
                    ->where(
                        'transaction_no',
                        'like',
                        $prefix.'%'
                    )
                    ->orderByDesc('id')
                    ->value('transaction_no');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }
}