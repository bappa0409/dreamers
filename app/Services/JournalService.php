<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalService
{
    public function __construct(
        protected AccountingService $accountingService
    ){}

    public function paginate(array $filters=[]): LengthAwarePaginator
    {
        return Transaction::query()
            ->select([
                'id',
                'transaction_no',
                'transaction_date',
                'type',
                'source_module',
                'description',
                'status',
                'created_by',
                'posted_by',
                'posted_at',
                'reversal_transaction_id',
                'reversed_by',
                'reversed_at',
            ])
            ->with([
                'creator:id,name',
                'poster:id,name',
            ])
            ->withSum(
                'entries as total_debit',
                'debit'
            )
            ->withSum(
                'entries as total_credit',
                'credit'
            )
            ->when(
                $filters['search']??null,
                function($query,$search){
                    $search=trim($search);

                    $query->where(function($query)use($search){
                        $query
                            ->where(
                                'transaction_no',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'description',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'source_module',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'type',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'entries.account',
                                function($account)use($search){
                                    $account
                                        ->where(
                                            'code',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                    });
                }
            )
            ->when(
                $filters['status']??null,
                fn($q,$value)=>$q->where(
                    'status',
                    $value
                )
            )
            ->when(
                $filters['type']??null,
                fn($q,$value)=>$q->where(
                    'type',
                    $value
                )
            )
            ->when(
                $filters['source_module']??null,
                fn($q,$value)=>$q->where(
                    'source_module',
                    $value
                )
            )
            ->when(
                $filters['from']??null,
                fn($q,$value)=>$q->whereDate(
                    'transaction_date',
                    '>=',
                    $value
                )
            )
            ->when(
                $filters['to']??null,
                fn($q,$value)=>$q->whereDate(
                    'transaction_date',
                    '<=',
                    $value
                )
            )
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(
                min(
                    max(
                        (int)($filters['per_page']??20),
                        5
                    ),
                    100
                )
            );
    }

    public function options(): array
    {
        return[
            'accounts'=>Account::active()
                ->posting()
                ->orderBy('code')
                ->get([
                    'id',
                    'code',
                    'name',
                    'type',
                    'sub_type',
                ]),

            'types'=>Transaction::query()
                ->whereNotNull('type')
                ->where('type','!=','')
                ->distinct()
                ->orderBy('type')
                ->pluck('type')
                ->values(),

            'source_modules'=>Transaction::query()
                ->whereNotNull('source_module')
                ->where('source_module','!=','')
                ->distinct()
                ->orderBy('source_module')
                ->pluck('source_module')
                ->values(),
        ];
    }

    public function summary(array $filters=[]): array
    {
        $base=Transaction::query()
            ->when(
                $filters['from']??null,
                fn($q,$value)=>$q->whereDate(
                    'transaction_date',
                    '>=',
                    $value
                )
            )
            ->when(
                $filters['to']??null,
                fn($q,$value)=>$q->whereDate(
                    'transaction_date',
                    '<=',
                    $value
                )
            );

        $total=(clone $base)->count();

        $posted=(clone $base)
            ->where('status','posted')
            ->count();

        $cancelled=(clone $base)
            ->where('status','cancelled')
            ->count();

        $reversed=(clone $base)
            ->whereNotNull('reversed_at')
            ->count();

        $manual=(clone $base)
            ->where(function($query){
                $query
                    ->where(
                        'source_module',
                        'manual'
                    )
                    ->orWhere(
                        'type',
                        'manual_journal'
                    );
            })
            ->count();

        $postedDebit=(float)DB::table(
            'transaction_entries as te'
        )
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
            ->when(
                $filters['from']??null,
                fn($q,$value)=>$q->whereDate(
                    't.transaction_date',
                    '>=',
                    $value
                )
            )
            ->when(
                $filters['to']??null,
                fn($q,$value)=>$q->whereDate(
                    't.transaction_date',
                    '<=',
                    $value
                )
            )
            ->sum('te.debit');

        return[
            'total'=>$total,
            'posted'=>$posted,
            'cancelled'=>$cancelled,
            'reversed'=>$reversed,
            'manual'=>$manual,
            'posted_debit'=>round(
                $postedDebit,
                2
            ),
        ];
    }

    public function createManual(
        array $data,
        int $userId
    ): Transaction{
        $entries=$this->normalizeEntries(
            $data['entries']
        );

        $payload=[
            'transaction_date'=>$data['transaction_date'],
            'type'=>'manual_journal',
            'source_module'=>'manual',
            'source_id'=>null,
            'reference_type'=>null,
            'reference_id'=>null,
            'description'=>trim(
                (string)$data['description']
            ),
            'user_id'=>$userId,
            'entries'=>$entries,
        ];

        if(
            !empty(
                $data['idempotency_key']
            )
        ){
            $payload['idempotency_key']=trim(
                (string)$data['idempotency_key']
            );
        }

        return $this->accountingService->post(
            $payload
        );
    }

    public function reverse(
        Transaction $transaction,
        array $data,
        int $userId
    ): Transaction{
        return DB::transaction(function()use(
            $transaction,
            $data,
            $userId
        ){
            $transaction=Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->with([
                    'entries:id,transaction_id,account_id,debit,credit',
                ])
                ->firstOrFail();

            if($transaction->status!=='posted'){
                throw ValidationException::withMessages([
                    'transaction'=>[
                        'Only a posted journal can be reversed.'
                    ],
                ]);
            }

            if(
                in_array(
                    $transaction->type,
                    [
                        'journal_reversal',
                        'income_reversal',
                        'expense_reversal',
                        'member_charge_reversal',
                    ],
                    true
                )
            ){
                throw ValidationException::withMessages([
                    'transaction'=>[
                        'A reversal journal cannot be reversed.'
                    ],
                ]);
            }

            if(
                $transaction->reversed_at||
                $transaction->reversal_transaction_id
            ){
                throw ValidationException::withMessages([
                    'transaction'=>[
                        'This journal has already been reversed.'
                    ],
                ]);
            }

            if($transaction->entries->isEmpty()){
                throw ValidationException::withMessages([
                    'transaction'=>[
                        'Journal has no entries to reverse.'
                    ],
                ]);
            }

            $reason=trim(
                (string)($data['reason']??'')
            );

            if($reason===''){
                throw ValidationException::withMessages([
                    'reason'=>[
                        'Reversal reason is required.'
                    ],
                ]);
            }

            $reversalDate=
                $data['transaction_date']
                ??now()->toDateString();

            if(
                $reversalDate<
                $transaction
                    ->transaction_date
                    ->toDateString()
            ){
                throw ValidationException::withMessages([
                    'transaction_date'=>[
                        'Reversal date cannot be earlier than the original journal date.'
                    ],
                ]);
            }

            $reversal=$this
                ->accountingService
                ->post([
                    'idempotency_key'=>
                        "journal:reversal:{$transaction->id}",

                    'transaction_date'=>
                        $reversalDate,

                    'type'=>
                        'journal_reversal',

                    'source_module'=>
                        'accounting',

                    'source_id'=>
                        $transaction->id,

                    'reference_type'=>
                        Transaction::class,

                    'reference_id'=>
                        $transaction->id,

                    'description'=>
                        "Reversal of {$transaction->transaction_no}: {$reason}",

                    'user_id'=>
                        $userId,

                    'entries'=>$transaction
                        ->entries
                        ->map(
                            fn($entry)=>[
                                'account_id'=>
                                    $entry->account_id,

                                'debit'=>
                                    (float)$entry->credit,

                                'credit'=>
                                    (float)$entry->debit,

                                'description'=>
                                    "Reversal of {$transaction->transaction_no}",
                            ]
                        )
                        ->all(),
                ]);

            $transaction->update([
                'reversal_transaction_id'=>
                    $reversal->id,

                'reversed_by'=>
                    $userId,

                'reversed_at'=>
                    now(),

                'cancel_reason'=>
                    $reason,
            ]);

            return $reversal->load([
                'entries.account:id,code,name,type,sub_type',
                'creator:id,name,email',
                'poster:id,name,email',
            ]);
        });
    }

    private function normalizeEntries(
        array $entries
    ): array{
        if(count($entries)<2){
            throw ValidationException::withMessages([
                'entries'=>[
                    'At least two journal lines are required.'
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

        $entries=collect($entries)
            ->map(function($entry,$index){
                $accountId=(int)(
                    $entry['account_id']??0
                );

                $debit=round(
                    (float)(
                        $entry['debit']??0
                    ),
                    2
                );

                $credit=round(
                    (float)(
                        $entry['credit']??0
                    ),
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
                            'Each line must have either debit or credit, not both.'
                        ],
                    ]);
                }

                return[
                    'account_id'=>$accountId,
                    'debit'=>$debit,
                    'credit'=>$credit,
                    'description'=>
                        isset(
                            $entry['description']
                        )
                            ?$this->nullableString(
                                $entry['description']
                            )
                            :null,
                ];
            })
            ->values();

        $accountIds=$entries
            ->pluck('account_id')
            ->unique()
            ->values();

        $validAccounts=Account::active()
            ->posting()
            ->whereIn(
                'id',
                $accountIds
            )
            ->count();

        if(
            $validAccounts!==
            $accountIds->count()
        ){
            throw ValidationException::withMessages([
                'entries'=>[
                    'One or more selected accounts are invalid, inactive or parent accounts.'
                ],
            ]);
        }

        /*
         * Compare cents instead of floating point.
         */
        $debitCents=$entries->sum(
            fn($entry)=>
                (int)round(
                    $entry['debit']*100
                )
        );

        $creditCents=$entries->sum(
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
                    'Total debit must equal total credit and must be greater than zero.'
                ],
            ]);
        }

        return $entries->all();
    }

    private function nullableString(
        mixed $value
    ): ?string{
        if($value===null){
            return null;
        }

        $value=trim(
            (string)$value
        );

        return $value===''
            ?null
            :$value;
    }
}