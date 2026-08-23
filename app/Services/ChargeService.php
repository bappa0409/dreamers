<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ChargePayment;
use App\Models\MemberCharge;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChargeService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected NumberSequenceService $numberSequenceService
    ){}

    public function create(
        array $data,
        int $userId
    ): MemberCharge{
        return DB::transaction(function()use(
            $data,
            $userId
        ){
            $incomeAccount=$this->incomeAccount(
                (int)$data['income_account_id']
            );

            $receivable=$this
                ->accountingService
                ->account('receivable');

            $amount=round(
                (float)$data['amount'],
                2
            );

            if($amount<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Charge amount must be greater than zero.'
                    ],
                ]);
            }

            if(
                !empty($data['due_date'])&&
                $data['due_date']<$data['charge_date']
            ){
                throw ValidationException::withMessages([
                    'due_date'=>[
                        'Due date cannot be earlier than charge date.'
                    ],
                ]);
            }

            $charge=MemberCharge::create([
                'charge_no'=>$this->generateChargeNumber(),
                'member_id'=>$data['member_id'],
                'income_account_id'=>$incomeAccount->id,
                'amount'=>$amount,
                'paid_amount'=>0,
                'charge_date'=>$data['charge_date'],
                'due_date'=>$data['due_date']??null,
                'charge_type'=>trim($data['charge_type']),
                'reference'=>$this->nullableString(
                    $data['reference']??null
                ),
                'description'=>$this->nullableString(
                    $data['description']??null
                ),
                'status'=>'unpaid',
                'created_by'=>$userId,
            ]);

            $journal=$this->accountingService->post([
                'idempotency_key'=>"charge:post:{$charge->id}",
                'transaction_date'=>$charge
                    ->charge_date
                    ->toDateString(),
                'type'=>'member_charge',
                'source_module'=>'member_charge',
                'source_id'=>$charge->id,
                'reference_type'=>MemberCharge::class,
                'reference_id'=>$charge->id,
                'description'=>$charge->description
                    ??"Member charge {$charge->charge_no}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$receivable->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>'Member receivable',
                    ],
                    [
                        'account_id'=>$incomeAccount->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>'Charge income',
                    ],
                ],
            ]);

            $charge->update([
                'finance_transaction_id'=>$journal->id,
            ]);

            return $this->freshCharge($charge);
        });
    }

    public function pay(
        MemberCharge $charge,
        array $data,
        int $userId
    ): ChargePayment{
        return DB::transaction(function()use(
            $charge,
            $data,
            $userId
        ){
            $charge=MemberCharge::query()
                ->whereKey($charge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(
                in_array(
                    $charge->status,
                    [
                        'paid',
                        'waived',
                        'cancelled',
                    ],
                    true
                )
            ){
                throw ValidationException::withMessages([
                    'charge'=>[
                        'This charge cannot receive payment.'
                    ],
                ]);
            }

            if(
                $data['payment_date']<
                $charge->charge_date->toDateString()
            ){
                throw ValidationException::withMessages([
                    'payment_date'=>[
                        'Payment date cannot be earlier than charge date.'
                    ],
                ]);
            }

            $amount=round(
                (float)$data['amount'],
                2
            );

            if($amount<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Payment amount must be greater than zero.'
                    ],
                ]);
            }

            $outstanding=max(
                round(
                    (float)$charge->amount-
                    (float)$charge->paid_amount,
                    2
                ),
                0
            );

            if($outstanding<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'This charge has no outstanding balance.'
                    ],
                ]);
            }

            if($amount>$outstanding){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Payment amount exceeds the outstanding balance.'
                    ],
                ]);
            }

            $receiveAccount=$this->cashBankAccount(
                (int)$data['receive_account_id']
            );

            $receivable=$this
                ->accountingService
                ->account('receivable');

            $payment=ChargePayment::create([
                'payment_no'=>$this->generatePaymentNumber(),
                'member_charge_id'=>$charge->id,
                'amount'=>$amount,
                'receive_account_id'=>$receiveAccount->id,
                'payment_date'=>$data['payment_date'],
                'payment_method'=>$data['payment_method']??null,
                'reference'=>$this->nullableString(
                    $data['reference']??null
                ),
                'description'=>$this->nullableString(
                    $data['description']??null
                ),
                'status'=>'posted',
                'created_by'=>$userId,
            ]);

            $journal=$this->accountingService->post([
                'idempotency_key'=>"charge:payment:{$payment->id}",
                'transaction_date'=>$payment
                    ->payment_date
                    ->toDateString(),
                'type'=>'charge_payment',
                'source_module'=>'charge_payment',
                'source_id'=>$payment->id,
                'reference_type'=>ChargePayment::class,
                'reference_id'=>$payment->id,
                'description'=>$payment->description
                    ??"Charge payment {$payment->payment_no}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$receiveAccount->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>'Charge payment received',
                    ],
                    [
                        'account_id'=>$receivable->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>'Member receivable settled',
                    ],
                ],
            ]);

            $payment->update([
                'finance_transaction_id'=>$journal->id,
            ]);

            $newPaid=round(
                (float)$charge->paid_amount+$amount,
                2
            );

            $newStatus=
                $newPaid>=(float)$charge->amount
                    ?'paid'
                    :'partial';

            $charge->update([
                'paid_amount'=>$newPaid,
                'status'=>$newStatus,
            ]);

            return $payment->fresh([
                'charge.member.user',
                'receiveAccount',
                'creator',
                'financeTransaction.entries.account',
            ]);
        });
    }

    public function update(
        MemberCharge $charge,
        array $data
    ): MemberCharge{
        return DB::transaction(function()use(
            $charge,
            $data
        ){
            $charge=MemberCharge::query()
                ->whereKey($charge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(
                in_array(
                    $charge->status,
                    [
                        'paid',
                        'waived',
                        'cancelled',
                    ],
                    true
                )
            ){
                throw ValidationException::withMessages([
                    'charge'=>[
                        'This charge can no longer be updated.'
                    ],
                ]);
            }

            if($charge->finance_transaction_id){
                foreach([
                    'member_id',
                    'income_account_id',
                    'amount',
                    'charge_date',
                ] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->changed(
                            $charge->{$field},
                            $data[$field]
                        )
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Posted financial data cannot be changed. Cancel the charge and create a new record instead.'
                            ],
                        ]);
                    }
                }
            }

            if(
                array_key_exists(
                    'income_account_id',
                    $data
                )
            ){
                $data['income_account_id']=$this
                    ->incomeAccount(
                        (int)$data['income_account_id']
                    )
                    ->id;
            }

            $chargeDate=
                array_key_exists(
                    'charge_date',
                    $data
                )
                    ?$data['charge_date']
                    :$charge->charge_date
                        ->toDateString();

            $dueDate=
                array_key_exists(
                    'due_date',
                    $data
                )
                    ?$data['due_date']
                    :$charge->due_date
                        ?->toDateString();

            if(
                $dueDate&&
                $dueDate<$chargeDate
            ){
                throw ValidationException::withMessages([
                    'due_date'=>[
                        'Due date cannot be earlier than charge date.'
                    ],
                ]);
            }

            $clean=[];

            foreach([
                'member_id',
                'income_account_id',
                'amount',
                'charge_date',
                'due_date',
                'charge_type',
                'reference',
                'description',
            ] as $field){
                if(
                    array_key_exists(
                        $field,
                        $data
                    )
                ){
                    $clean[$field]=$data[$field];
                }
            }

            if(
                array_key_exists(
                    'amount',
                    $clean
                )
            ){
                $clean['amount']=round(
                    (float)$clean['amount'],
                    2
                );
            }

            if(
                array_key_exists(
                    'charge_type',
                    $clean
                )
            ){
                $clean['charge_type']=trim(
                    (string)$clean['charge_type']
                );
            }

            foreach([
                'reference',
                'description',
            ] as $field){
                if(
                    array_key_exists(
                        $field,
                        $clean
                    )
                ){
                    $clean[$field]=$this->nullableString(
                        $clean[$field]
                    );
                }
            }

            $charge->update($clean);

            return $this->freshCharge($charge);
        });
    }

    public function cancel(
        MemberCharge $charge,
        string $reason,
        int $userId
    ): MemberCharge{
        return DB::transaction(function()use(
            $charge,
            $reason,
            $userId
        ){
            $charge=MemberCharge::query()
                ->whereKey($charge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($charge->status==='cancelled'){
                throw ValidationException::withMessages([
                    'charge'=>[
                        'Charge is already cancelled.'
                    ],
                ]);
            }

            if($charge->status==='waived'){
                throw ValidationException::withMessages([
                    'charge'=>[
                        'Waived charge cannot be cancelled.'
                    ],
                ]);
            }

            if(
                (float)$charge->paid_amount>0||
                $charge->payments()
                    ->where('status','posted')
                    ->exists()
            ){
                throw ValidationException::withMessages([
                    'charge'=>[
                        'A charge with payments cannot be cancelled directly.'
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

            $charge->loadMissing(
                'financeTransaction.entries'
            );

            if($charge->financeTransaction){
                $entries=$charge
                    ->financeTransaction
                    ->entries
                    ->map(
                        fn($entry)=>[
                            'account_id'=>$entry->account_id,
                            'debit'=>(float)$entry->credit,
                            'credit'=>(float)$entry->debit,
                            'description'=>
                                "Reversal of {$charge->charge_no}",
                        ]
                    )
                    ->all();

                $this->accountingService->post([
                    'idempotency_key'=>
                        "charge:cancel:{$charge->id}",
                    'transaction_date'=>
                        now()->toDateString(),
                    'type'=>'member_charge_reversal',
                    'source_module'=>'member_charge',
                    'source_id'=>$charge->id,
                    'reference_type'=>MemberCharge::class,
                    'reference_id'=>$charge->id,
                    'description'=>
                        "Cancellation of {$charge->charge_no}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$entries,
                ]);
            }

            $charge->update([
                'status'=>'cancelled',
            ]);

            return $this->freshCharge($charge);
        });
    }

    public function waive(
        MemberCharge $charge,
        string $reason,
        int $userId
    ): MemberCharge{
        return DB::transaction(function()use(
            $charge,
            $reason,
            $userId
        ){
            $charge=MemberCharge::query()
                ->whereKey($charge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(
                in_array(
                    $charge->status,
                    [
                        'paid',
                        'waived',
                        'cancelled',
                    ],
                    true
                )
            ){
                throw ValidationException::withMessages([
                    'charge'=>[
                        'This charge cannot be waived.'
                    ],
                ]);
            }

            if(
                (float)$charge->paid_amount>0||
                $charge->payments()
                    ->where('status','posted')
                    ->exists()
            ){
                throw ValidationException::withMessages([
                    'charge'=>[
                        'Partially paid charges cannot be fully waived.'
                    ],
                ]);
            }

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'reason'=>[
                        'Waiver reason is required.'
                    ],
                ]);
            }

            $charge->loadMissing(
                'financeTransaction.entries'
            );

            if($charge->financeTransaction){
                $entries=$charge
                    ->financeTransaction
                    ->entries
                    ->map(
                        fn($entry)=>[
                            'account_id'=>$entry->account_id,
                            'debit'=>(float)$entry->credit,
                            'credit'=>(float)$entry->debit,
                            'description'=>
                                "Waiver of {$charge->charge_no}",
                        ]
                    )
                    ->all();

                $this->accountingService->post([
                    'idempotency_key'=>
                        "charge:waive:{$charge->id}",
                    'transaction_date'=>
                        now()->toDateString(),
                    'type'=>'member_charge_waiver',
                    'source_module'=>'member_charge',
                    'source_id'=>$charge->id,
                    'reference_type'=>MemberCharge::class,
                    'reference_id'=>$charge->id,
                    'description'=>
                        "Waiver of {$charge->charge_no}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$entries,
                ]);
            }

            $charge->update([
                'status'=>'waived',
            ]);

            return $this->freshCharge($charge);
        });
    }

    public function delete(
        MemberCharge $charge
    ): void{
        DB::transaction(function()use(
            $charge
        ){
            $charge=MemberCharge::query()
                ->whereKey($charge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(
                $charge->finance_transaction_id||
                $charge->payments()->exists()
            ){
                throw ValidationException::withMessages([
                    'charge'=>[
                        'Posted charge history cannot be deleted. Cancel or waive the charge instead.'
                    ],
                ]);
            }

            $charge->delete();
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
        int $accountId
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->whereIn(
                'sub_type',
                [
                    'cash',
                    'bank',
                ]
            )
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'receive_account_id'=>[
                    'Receive account must be an active Cash or Bank posting account.'
                ],
            ]);
        }

        return $account;
    }

    protected function freshCharge(
        MemberCharge $charge
    ): MemberCharge{
        return $charge->fresh([
            'member.user',
            'incomeAccount',
            'creator',
            'financeTransaction.entries.account',
            'payments.receiveAccount',
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

        if(
            is_numeric($current)&&
            is_numeric($new)
        ){
            return round(
                (float)$current,
                2
            )!==round(
                (float)$new,
                2
            );
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

        $value=trim(
            (string)$value
        );

        return $value===''
            ?null
            :$value;
    }

    private function generateChargeNumber(): string
    {
        $month=now()->format('Ym');
        $prefix="CHG-{$month}-";

        return $this->numberSequenceService->next(
            key:"charge:{$month}",
            prefix:$prefix,
            digits:6,
            initialValue:function()use($prefix){
                $last=MemberCharge::query()
                    ->where(
                        'charge_no',
                        'like',
                        $prefix.'%'
                    )
                    ->orderByDesc('id')
                    ->value('charge_no');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }

    private function generatePaymentNumber(): string
    {
        $month=now()->format('Ym');
        $prefix="CPY-{$month}-";

        return $this->numberSequenceService->next(
            key:"charge-payment:{$month}",
            prefix:$prefix,
            digits:6,
            initialValue:function()use($prefix){
                $last=ChargePayment::query()
                    ->where(
                        'payment_no',
                        'like',
                        $prefix.'%'
                    )
                    ->orderByDesc('id')
                    ->value('payment_no');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }
}