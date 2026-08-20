<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalService
{
    public function paginate(array $filters=[]): LengthAwarePaginator
    {
        return Transaction::query()
            ->with([
                'creator:id,name,email',
                'poster:id,name,email',
                'entries.account:id,code,name,type,sub_type'
            ])
            ->withSum('entries as total_debit','debit')
            ->withSum('entries as total_credit','credit')
            ->when($filters['search']??null,function($query,$search){
                $search=trim($search);

                $query->where(function($q)use($search){
                    $q->where('transaction_no','like',"%{$search}%")
                        ->orWhere('description','like',"%{$search}%")
                        ->orWhere('source_module','like',"%{$search}%")
                        ->orWhere('type','like',"%{$search}%")
                        ->orWhereHas('entries.account',function($aq)use($search){
                            $aq->where('code','like',"%{$search}%")
                                ->orWhere('name','like',"%{$search}%");
                        })
                        ->orWhereHas('creator',function($uq)use($search){
                            $uq->where('name','like',"%{$search}%")
                                ->orWhere('email','like',"%{$search}%");
                        });
                });
            })
            ->when(
                $filters['status']??null,
                fn($query,$status)=>$query->where('status',$status)
            )
            ->when(
                $filters['type']??null,
                fn($query,$type)=>$query->where('type',$type)
            )
            ->when(
                $filters['source_module']??null,
                fn($query,$module)=>$query->where('source_module',$module)
            )
            ->when(
                $filters['from']??null,
                fn($query,$from)=>$query->whereDate('transaction_date','>=',$from)
            )
            ->when(
                $filters['to']??null,
                fn($query,$to)=>$query->whereDate('transaction_date','<=',$to)
            )
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(
                min((int)($filters['per_page']??20),100)
            );
    }

    public function options(): array
    {
        return[
            'accounts'=>Account::query()
                ->active()
                ->posting()
                ->orderBy('code')
                ->get([
                    'id',
                    'code',
                    'name',
                    'type',
                    'sub_type'
                ]),
            'types'=>Transaction::query()
                ->whereNotNull('type')
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
                ->values()
        ];
    }

    public function summary(array $filters=[]): array
    {
        $query=Transaction::query();

        if(!empty($filters['from'])){
            $query->whereDate(
                'transaction_date',
                '>=',
                $filters['from']
            );
        }

        if(!empty($filters['to'])){
            $query->whereDate(
                'transaction_date',
                '<=',
                $filters['to']
            );
        }

        $row=(clone $query)
            ->selectRaw("
                COUNT(*) total,
                SUM(CASE WHEN status='posted' THEN 1 ELSE 0 END) posted,
                SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled,
                SUM(CASE WHEN source_module='manual' OR type='manual_journal' THEN 1 ELSE 0 END) manual
            ")
            ->first();

        $debit=(float)DB::table('transaction_entries')
            ->join(
                'transactions',
                'transactions.id',
                '=',
                'transaction_entries.transaction_id'
            )
            ->where('transactions.status','posted')
            ->when(
                $filters['from']??null,
                fn($q,$from)=>$q->whereDate(
                    'transactions.transaction_date',
                    '>=',
                    $from
                )
            )
            ->when(
                $filters['to']??null,
                fn($q,$to)=>$q->whereDate(
                    'transactions.transaction_date',
                    '<=',
                    $to
                )
            )
            ->sum('transaction_entries.debit');

        return[
            'total'=>(int)($row->total??0),
            'posted'=>(int)($row->posted??0),
            'cancelled'=>(int)($row->cancelled??0),
            'manual'=>(int)($row->manual??0),
            'posted_debit'=>$debit
        ];
    }

    public function createManual(
        array $data,
        int $userId
    ): Transaction{
        $entries=$this->normalizeEntries(
            $data['entries']
        );

        return app(AccountingService::class)->post([
            'transaction_date'=>$data['transaction_date'],
            'type'=>'manual_journal',
            'source_module'=>'manual',
            'source_id'=>null,
            'reference_type'=>null,
            'reference_id'=>null,
            'description'=>$data['description'],
            'user_id'=>$userId,
            'entries'=>$entries
        ]);
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
                ->with('entries')
                ->firstOrFail();

            if($transaction->status!=='posted'){
                throw ValidationException::withMessages([
                    'transaction'=>[
                        'Only a posted journal can be reversed.'
                    ]
                ]);
            }

            if(!$transaction->entries->count()){
                throw ValidationException::withMessages([
                    'transaction'=>[
                        'Journal has no entries to reverse.'
                    ]
                ]);
            }

            $alreadyReversed=Transaction::query()
                ->where('type','journal_reversal')
                ->where('reference_type',Transaction::class)
                ->where('reference_id',$transaction->id)
                ->where('status','posted')
                ->exists();

            if($alreadyReversed){
                throw ValidationException::withMessages([
                    'transaction'=>[
                        'This journal has already been reversed.'
                    ]
                ]);
            }

            $reversal=app(AccountingService::class)->post([
                'transaction_date'=>$data['transaction_date']
                    ??now()->toDateString(),
                'type'=>'journal_reversal',
                'source_module'=>'accounting',
                'source_id'=>$transaction->id,
                'reference_type'=>Transaction::class,
                'reference_id'=>$transaction->id,
                'description'=>'Reversal of '.
                    $transaction->transaction_no.
                    ' - '.
                    $data['reason'],
                'user_id'=>$userId,
                'entries'=>$transaction->entries
                    ->map(fn($entry)=>[
                        'account_id'=>$entry->account_id,
                        'debit'=>(float)$entry->credit,
                        'credit'=>(float)$entry->debit,
                        'description'=>'Reversal of '.
                            $transaction->transaction_no
                    ])
                    ->all()
            ]);

            $transaction->update([
                'status'=>'cancelled',
                'cancel_reason'=>$data['reason']
            ]);

            return $reversal->load([
                'entries.account',
                'creator',
                'poster'
            ]);
        });
    }

    private function normalizeEntries(array $entries): array
    {
        $entries=collect($entries)
            ->map(fn($entry)=>[
                'account_id'=>(int)($entry['account_id']??0),
                'debit'=>round(
                    (float)($entry['debit']??0),
                    2
                ),
                'credit'=>round(
                    (float)($entry['credit']??0),
                    2
                ),
                'description'=>isset($entry['description'])
                    ?trim((string)$entry['description'])
                    :null
            ])
            ->filter(
                fn($entry)=>
                    $entry['account_id']>0&&
                    (
                        $entry['debit']>0||
                        $entry['credit']>0
                    )
            )
            ->values();

        if($entries->count()<2){
            throw ValidationException::withMessages([
                'entries'=>[
                    'At least two valid journal lines are required.'
                ]
            ]);
        }

        $accountIds=$entries
            ->pluck('account_id')
            ->unique();

        $validAccounts=Account::query()
            ->active()
            ->posting()
            ->whereIn('id',$accountIds)
            ->count();

        if($validAccounts!==$accountIds->count()){
            throw ValidationException::withMessages([
                'entries'=>[
                    'One or more selected accounts are invalid, inactive or parent accounts.'
                ]
            ]);
        }

        $debit=round(
            $entries->sum('debit'),
            2
        );

        $credit=round(
            $entries->sum('credit'),
            2
        );

        if($debit<=0||$debit!==$credit){
            throw ValidationException::withMessages([
                'entries'=>[
                    'Total debit must equal total credit and must be greater than zero.'
                ]
            ]);
        }

        foreach($entries as $index=>$entry){
            if(
                (
                    $entry['debit']>0&&
                    $entry['credit']>0
                )||
                (
                    $entry['debit']<=0&&
                    $entry['credit']<=0
                )
            ){
                throw ValidationException::withMessages([
                    "entries.{$index}"=>[
                        'Each line must have either debit or credit, not both.'
                    ]
                ]);
            }
        }

        return $entries->all();
    }
}