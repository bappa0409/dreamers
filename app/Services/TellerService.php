<?php

namespace App\Services;

use App\Models\Account;
use App\Models\TellerClosing;
use App\Models\TellerTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TellerService
{
    public function __construct(
        protected AccountingService $accountingService
    ){}

    public function receive(
        array $data,
        int $tellerId
    ): TellerTransaction{
        return DB::transaction(function()use(
            $data,
            $tellerId
        ){
            $this->ensureDayOpen(
                $tellerId,
                $data['transaction_date']
                    ??now()->toDateString()
            );

            $cashAccount=$this->cashAccount(
                (int)$data['cash_account_id']
            );

            $counterAccount=$this->receiveCounterAccount(
                (int)$data['counter_account_id']
            );

            $transaction=$this->createTransaction(
                data:$data,
                tellerId:$tellerId,
                type:'receive',
                cashAccountId:$cashAccount->id,
                counterAccountId:$counterAccount->id
            );

            $journal=$this->accountingService->post([
                'transaction_date'=>$transaction
                    ->transaction_date
                    ->toDateString(),

                'type'=>'teller_receive',

                'source_module'=>'teller',

                'source_id'=>$transaction->id,

                'reference_type'=>TellerTransaction::class,

                'reference_id'=>$transaction->id,

                'description'=>$transaction->description
                    ?:$transaction->purpose
                    ?:'Teller cash receive',

                'user_id'=>$tellerId,

                'entries'=>[
                    [
                        'account_id'=>$cashAccount->id,
                        'debit'=>$transaction->amount,
                        'credit'=>0,
                        'description'=>'Teller cash received'
                    ],
                    [
                        'account_id'=>$counterAccount->id,
                        'debit'=>0,
                        'credit'=>$transaction->amount,
                        'description'=>$transaction->description
                            ?: $transaction->purpose
                            ?: 'Teller receive'
                    ]
                ]
            ]);

            $transaction->update([
                'finance_transaction_id'=>$journal->id
            ]);

            return $this->freshTransaction(
                $transaction
            );
        });
    }

    public function payment(
        array $data,
        int $tellerId
    ): TellerTransaction{
        return DB::transaction(function()use(
            $data,
            $tellerId
        ){
            $date=$data['transaction_date']
                ??now()->toDateString();

            $this->ensureDayOpen(
                $tellerId,
                $date
            );

            $cashAccount=$this->cashAccount(
                (int)$data['cash_account_id']
            );

            $counterAccount=$this->paymentCounterAccount(
                (int)$data['counter_account_id']
            );

            $balance=$this->balance(
                $tellerId,
                $cashAccount->id
            );

            if(
                round((float)$data['amount'],2)>
                round($balance['balance'],2)
            ){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Insufficient teller balance.'
                    ]
                ]);
            }

            $transaction=$this->createTransaction(
                data:$data,
                tellerId:$tellerId,
                type:'payment',
                cashAccountId:$cashAccount->id,
                counterAccountId:$counterAccount->id
            );

            $journal=$this->accountingService->post([
                'transaction_date'=>$transaction
                    ->transaction_date
                    ->toDateString(),

                'type'=>'teller_payment',

                'source_module'=>'teller',

                'source_id'=>$transaction->id,

                'reference_type'=>TellerTransaction::class,

                'reference_id'=>$transaction->id,

                'description'=>$transaction->description
                    ?:$transaction->purpose
                    ?:'Teller cash payment',

                'user_id'=>$tellerId,

                'entries'=>[
                    [
                        'account_id'=>$counterAccount->id,
                        'debit'=>$transaction->amount,
                        'credit'=>0,
                        'description'=>$transaction->description
                            ?: $transaction->purpose
                            ?: 'Teller payment'
                    ],
                    [
                        'account_id'=>$cashAccount->id,
                        'debit'=>0,
                        'credit'=>$transaction->amount,
                        'description'=>'Teller cash paid'
                    ]
                ]
            ]);

            $transaction->update([
                'finance_transaction_id'=>$journal->id
            ]);

            return $this->freshTransaction(
                $transaction
            );
        });
    }

    public function balance(
        int $tellerId,
        ?int $cashAccountId=null
    ): array{
        $receive=TellerTransaction::query()
            ->where('teller_id',$tellerId)
            ->where('type','receive')
            ->where('status','completed')
            ->when(
                $cashAccountId,
                fn($q)=>$q->where(
                    'cash_account_id',
                    $cashAccountId
                )
            )
            ->sum('amount');

        $payment=TellerTransaction::query()
            ->where('teller_id',$tellerId)
            ->where('type','payment')
            ->where('status','completed')
            ->when(
                $cashAccountId,
                fn($q)=>$q->where(
                    'cash_account_id',
                    $cashAccountId
                )
            )
            ->sum('amount');

        return[
            'total_received'=>(float)$receive,
            'total_paid'=>(float)$payment,
            'balance'=>(float)$receive-(float)$payment
        ];
    }

    public function cancel(
        TellerTransaction $transaction,
        int $userId
    ): TellerTransaction{
        return DB::transaction(function()use(
            $transaction,
            $userId
        ){
            $transaction=TellerTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($transaction->status==='cancelled'){
                throw ValidationException::withMessages([
                    'transaction'=>[
                        'Teller transaction is already cancelled.'
                    ]
                ]);
            }

            $this->ensureDayOpen(
                $transaction->teller_id,
                $transaction
                    ->transaction_date
                    ->toDateString()
            );

            if($transaction->finance_transaction_id){
                $original=$transaction
                    ->financeTransaction()
                    ->with('entries')
                    ->firstOrFail();

                $this->accountingService->post([
                    'transaction_date'=>now()
                        ->toDateString(),

                    'type'=>$transaction->type==='receive'
                        ?'teller_receive_reversal'
                        :'teller_payment_reversal',

                    'source_module'=>'teller',

                    'source_id'=>$transaction->id,

                    'reference_type'=>TellerTransaction::class,

                    'reference_id'=>$transaction->id,

                    'description'=>
                        "Reversal of {$original->transaction_no} - {$transaction->transaction_no}",

                    'user_id'=>$userId,

                    'entries'=>$original
                        ->entries
                        ->map(fn($entry)=>[
                            'account_id'=>$entry->account_id,
                            'debit'=>(float)$entry->credit,
                            'credit'=>(float)$entry->debit,
                            'description'=>
                                'Teller transaction reversal'
                        ])
                        ->all()
                ]);
            }

            $transaction->update([
                'status'=>'cancelled'
            ]);

            return $this->freshTransaction(
                $transaction
            );
        });
    }

    public function options(): array
    {
        return[
            'cash_accounts'=>Account::query()
                ->active()
                ->posting()
                ->where('sub_type','cash')
                ->orderBy('code')
                ->get([
                    'id',
                    'code',
                    'name',
                    'type',
                    'sub_type'
                ]),

            'receive_accounts'=>Account::query()
                ->active()
                ->posting()
                ->whereIn(
                    'type',
                    [
                        'income',
                        'liability',
                        'equity'
                    ]
                )
                ->orderBy('code')
                ->get([
                    'id',
                    'code',
                    'name',
                    'type',
                    'sub_type'
                ]),

            'payment_accounts'=>Account::query()
                ->active()
                ->posting()
                ->whereIn(
                    'type',
                    [
                        'expense',
                        'asset',
                        'liability'
                    ]
                )
                ->where('sub_type','!=','cash')
                ->orderBy('code')
                ->get([
                    'id',
                    'code',
                    'name',
                    'type',
                    'sub_type'
                ])
        ];
    }

    protected function createTransaction(
        array $data,
        int $tellerId,
        string $type,
        int $cashAccountId,
        int $counterAccountId
    ): TellerTransaction{
        return TellerTransaction::create([
            'transaction_no'=>$this->generateNumber(),
            'teller_id'=>$tellerId,
            'member_id'=>$data['member_id']??null,
            'cash_account_id'=>$cashAccountId,
            'counter_account_id'=>$counterAccountId,
            'type'=>$type,
            'amount'=>$data['amount'],
            'purpose'=>$data['purpose']??null,
            'description'=>$data['description']??null,
            'transaction_date'=>$data['transaction_date']
                ??now()->toDateString(),
            'status'=>'completed'
        ]);
    }

    protected function generateNumber(): string
    {
        $last=TellerTransaction::query()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('transaction_no');

        $next=$last
            ?((int)substr($last,-6))+1
            :1;

        return 'TELLER-'.str_pad(
            (string)$next,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function cashAccount(
        int $accountId
    ): Account{
        $account=Account::query()
            ->active()
            ->posting()
            ->whereKey($accountId)
            ->where('type','asset')
            ->where('sub_type','cash')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'cash_account_id'=>[
                    'Selected teller account must be an active posting Cash account.'
                ]
            ]);
        }

        return $account;
    }

    protected function receiveCounterAccount(
        int $accountId
    ): Account{
        $account=Account::query()
            ->active()
            ->posting()
            ->whereKey($accountId)
            ->whereIn(
                'type',
                [
                    'income',
                    'liability',
                    'equity'
                ]
            )
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'counter_account_id'=>[
                    'Select a valid active receive account.'
                ]
            ]);
        }

        return $account;
    }

    protected function paymentCounterAccount(
        int $accountId
    ): Account{
        $account=Account::query()
            ->active()
            ->posting()
            ->whereKey($accountId)
            ->whereIn(
                'type',
                [
                    'expense',
                    'asset',
                    'liability'
                ]
            )
            ->where(function($q){
                $q->whereNull('sub_type')
                    ->orWhere(
                        'sub_type',
                        '!=',
                        'cash'
                    );
            })
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'counter_account_id'=>[
                    'Select a valid active payment account.'
                ]
            ]);
        }

        return $account;
    }

    protected function ensureDayOpen(
        int $tellerId,
        string $date
    ): void{
        $closing=TellerClosing::query()
            ->where('teller_id',$tellerId)
            ->whereDate('closing_date',$date)
            ->where('status','closed')
            ->exists();

        if($closing){
            throw ValidationException::withMessages([
                'closing'=>[
                    'Teller is closed for the selected date. Reopen the day before posting transactions.'
                ]
            ]);
        }
    }

    protected function freshTransaction(
        TellerTransaction $transaction
    ): TellerTransaction{
        return $transaction->fresh([
            'member.user',
            'teller',
            'cashAccount',
            'counterAccount',
            'financeTransaction.entries.account'
        ]);
    }
}