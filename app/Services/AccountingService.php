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

    /**
     * Create a transaction in 'draft' status: entries are validated and
     * stored, but nothing is posted to the ledger (Account::postedEntries()
     * only sums 'posted' transactions) until postDraftTransaction() is
     * called. Used by manual journal entries, which now require approval
     * before posting.
     */
    public function createDraft(array $data): Transaction
    {
        return DB::transaction(function()use($data){
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

                'status'=>'draft',
                'created_by'=>$userId,
            ]);

            $transaction->entries()->createMany(
                $entries
            );

            return $transaction->load(
                'entries.account'
            );
        });
    }

    /**
     * Move a 'draft' transaction into 'posted' status once its approval
     * request has been approved. Re-validates that its accounts are
     * still active/postable at approval time.
     */
    public function postDraftTransaction(
        Transaction $transaction,
        int $userId
    ): Transaction{
        return DB::transaction(function()use(
            $transaction,
            $userId
        ){
            $transaction=Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->with('entries')
                ->firstOrFail();

            if($transaction->status!=='draft'){
                throw ValidationException::withMessages([
                    'transaction'=>[
                        'This journal entry is not awaiting approval.'
                    ],
                ]);
            }

            $this->validateAccounts(
                $transaction->entries
                    ->map(fn($entry)=>[
                        'account_id'=>$entry->account_id,
                    ])
                    ->all()
            );

            $transaction->update([
                'status'=>'posted',
                'posted_at'=>now(),
                'posted_by'=>$userId,
            ]);

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
    }

    public function account(string $subType): Account
    {
        $accounts=Account::query()
            ->where('sub_type',$subType)
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->orderBy('id')
            ->get();

        if($accounts->isEmpty()){
            throw ValidationException::withMessages([
                'account'=>[
                    "Active posting account for '{$subType}' was not found."
                ],
            ]);
        }

        // More than one active leaf account shares this sub_type - this is a
        // data problem (usually a seeder run twice / two seeders creating the
        // same system account under different codes), not something we
        // should silently resolve by picking the first row. Postings must be
        // unambiguous, so fail loudly with enough detail to fix the data
        // (see the finance:merge-duplicate-accounts command).
        if($accounts->count()>1){
            $codes=$accounts
                ->pluck('code')
                ->implode(', ');

            throw ValidationException::withMessages([
                'account'=>[
                    "Multiple active posting accounts share sub_type '{$subType}' (codes: {$codes}). Run `php artisan finance:merge-duplicate-accounts` to resolve this before posting."
                ],
            ]);
        }

        return $accounts->first();
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