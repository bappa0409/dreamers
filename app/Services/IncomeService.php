<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Income;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class IncomeService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected NumberSequenceService $numberSequenceService
    ){}

    public function create(array $data,int $userId): Income
    {
        return DB::transaction(function()use($data,$userId){
            $incomeAccount=$this->incomeAccount(
                (int)$data['income_account_id']
            );

            $receiveAccount=$this->cashBankAccount(
                (int)$data['receive_account_id'],
                'receive_account_id'
            );

            $amount=round(
                (float)$data['amount'],
                2
            );

            if($amount<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Income amount must be greater than zero.'
                    ],
                ]);
            }

            $income=Income::create([
                'income_no'=>$this->generateNumber(),
                'member_id'=>$data['member_id']??null,
                'income_account_id'=>$incomeAccount->id,
                'receive_account_id'=>$receiveAccount->id,
                'amount'=>$amount,
                'income_date'=>$data['income_date'],
                'reference'=>$this->nullableString(
                    $data['reference']??null
                ),
                'description'=>$this->nullableString(
                    $data['description']??null
                ),
                'attachment'=>$data['attachment']??null,
                'created_by'=>$userId,
                'status'=>'posted',
            ]);

            $journal=$this->accountingService->post([
                'idempotency_key'=>"income:post:{$income->id}",
                'transaction_date'=>$income
                    ->income_date
                    ->toDateString(),
                'type'=>'income',
                'source_module'=>'income',
                'source_id'=>$income->id,
                'reference_type'=>Income::class,
                'reference_id'=>$income->id,
                'description'=>$income->description
                    ??"Income {$income->income_no}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$receiveAccount->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>'Income received',
                    ],
                    [
                        'account_id'=>$incomeAccount->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>'Income recognized',
                    ],
                ],
            ]);

            $income->update([
                'finance_transaction_id'=>$journal->id,
            ]);

            return $this->freshIncome($income);
        });
    }

    public function update(
        Income $income,
        array $data
    ): Income{
        return DB::transaction(function()use(
            $income,
            $data
        ){
            $income=Income::query()
                ->whereKey($income->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($income->status==='cancelled'){
                throw ValidationException::withMessages([
                    'income'=>[
                        'Cancelled income cannot be updated.'
                    ],
                ]);
            }

            if($income->finance_transaction_id){
                foreach([
                    'income_account_id',
                    'receive_account_id',
                    'amount',
                    'income_date',
                ] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->changed(
                            $income->{$field},
                            $data[$field]
                        )
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Posted financial data cannot be changed. Cancel the income and create a new record instead.'
                            ],
                        ]);
                    }
                }
            }

            $update=[];

            foreach([
                'member_id',
                'reference',
                'description',
                'attachment',
            ] as $field){
                if(array_key_exists($field,$data)){
                    $update[$field]=$data[$field];
                }
            }

            foreach([
                'reference',
                'description',
            ] as $field){
                if(array_key_exists($field,$update)){
                    $update[$field]=$this->nullableString(
                        $update[$field]
                    );
                }
            }

            if(
                array_key_exists('attachment',$update)&&
                $income->attachment&&
                $income->attachment!==$update['attachment']
            ){
                Storage::disk('public')->delete(
                    $income->attachment
                );
            }

            $income->update($update);

            return $this->freshIncome($income);
        });
    }

    public function cancel(
        Income $income,
        string $reason,
        int $userId
    ): Income{
        return DB::transaction(function()use(
            $income,
            $reason,
            $userId
        ){
            $income=Income::query()
                ->whereKey($income->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($income->status==='cancelled'){
                throw ValidationException::withMessages([
                    'income'=>[
                        'Income is already cancelled.'
                    ],
                ]);
            }

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'reason'=>[
                        'Cancellation reason is required.'
                    ],
                ]);
            }

            $income->loadMissing(
                'financeTransaction.entries'
            );

            if($income->financeTransaction){
                $entries=$income
                    ->financeTransaction
                    ->entries
                    ->map(fn($entry)=>[
                        'account_id'=>$entry->account_id,
                        'debit'=>(float)$entry->credit,
                        'credit'=>(float)$entry->debit,
                        'description'=>
                            "Reversal of {$income->income_no}",
                    ])
                    ->all();

                $this->accountingService->post([
                    'idempotency_key'=>
                        "income:cancel:{$income->id}",
                    'transaction_date'=>now()->toDateString(),
                    'type'=>'income_reversal',
                    'source_module'=>'income',
                    'source_id'=>$income->id,
                    'reference_type'=>Income::class,
                    'reference_id'=>$income->id,
                    'description'=>
                        "Cancellation of {$income->income_no}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$entries,
                ]);
            }

            $income->update([
                'status'=>'cancelled',
            ]);

            return $this->freshIncome($income);
        });
    }

    public function delete(Income $income): void
    {
        DB::transaction(function()use($income){
            $income=Income::query()
                ->whereKey($income->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($income->finance_transaction_id){
                throw ValidationException::withMessages([
                    'income'=>[
                        'Posted income cannot be deleted. Cancel it using a reversal instead.'
                    ],
                ]);
            }

            $attachment=$income->attachment;

            $income->delete();

            if($attachment){
                Storage::disk('public')->delete(
                    $attachment
                );
            }
        });
    }

    protected function incomeAccount(
        int $accountId
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->where('type','income')
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'income_account_id'=>[
                    'Selected account must be an active Income posting account.'
                ],
            ]);
        }

        return $account;
    }

    protected function cashBankAccount(
        int $accountId,
        string $field
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->whereIn('sub_type',[
                'cash',
                'bank',
            ])
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                $field=>[
                    'Selected account must be an active Cash or Bank posting account.'
                ],
            ]);
        }

        return $account;
    }

    protected function freshIncome(
        Income $income
    ): Income{
        return $income->fresh([
            'member.user',
            'incomeAccount',
            'receiveAccount',
            'financeTransaction.entries.account',
            'creator',
        ]);
    }

    protected function changed(
        mixed $current,
        mixed $new
    ): bool{
        if($current instanceof \DateTimeInterface){
            return $current->format('Y-m-d')!==
                (string)$new;
        }

        if(is_numeric($current)&&is_numeric($new)){
            return round((float)$current,2)!==
                round((float)$new,2);
        }

        return (string)($current??'')!==
            (string)($new??'');
    }

    protected function nullableString(
        mixed $value
    ): ?string{
        if($value===null){
            return null;
        }

        $value=trim((string)$value);

        return $value===''?null:$value;
    }

    private function generateNumber(): string
    {
        $month=now()->format('Ym');
        $prefix="INC-{$month}-";

        return $this->numberSequenceService->next(
            key:"income:{$month}",
            prefix:$prefix,
            digits:6,
            initialValue:function()use($prefix){
                $last=Income::query()
                    ->where(
                        'income_no',
                        'like',
                        $prefix.'%'
                    )
                    ->orderByDesc('id')
                    ->value('income_no');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }
}