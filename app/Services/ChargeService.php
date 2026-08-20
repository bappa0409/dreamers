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
        protected AccountingService $accounting
    ){}

    public function create(array $data,int $userId): MemberCharge
    {
        return DB::transaction(function()use($data,$userId){
            $incomeAccount=Account::query()
                ->whereKey($data['income_account_id'])
                ->where('type','income')
                ->where('is_active',true)
                ->first();

            if(!$incomeAccount){
                throw ValidationException::withMessages([
                    'income_account_id'=>['Invalid income account.'],
                ]);
            }

            $receivable=$this->accounting->account('receivable');

            $charge=MemberCharge::create([
                'charge_no'=>$this->generateChargeNumber(),
                'member_id'=>$data['member_id'],
                'income_account_id'=>$incomeAccount->id,
                'amount'=>$data['amount'],
                'paid_amount'=>0,
                'charge_date'=>$data['charge_date'],
                'due_date'=>$data['due_date']??null,
                'charge_type'=>$data['charge_type'],
                'reference'=>$data['reference']??null,
                'description'=>$data['description']??null,
                'status'=>'unpaid',
                'created_by'=>$userId,
            ]);

            $journal=$this->accounting->post([
                'transaction_date'=>$charge->charge_date->toDateString(),
                'type'=>'member_charge',
                'source_module'=>'member_charge',
                'source_id'=>$charge->id,
                'reference_type'=>MemberCharge::class,
                'reference_id'=>$charge->id,
                'description'=>$charge->description??"Member charge {$charge->charge_no}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$receivable->id,
                        'debit'=>$charge->amount,
                        'credit'=>0,
                        'description'=>'Member receivable',
                    ],
                    [
                        'account_id'=>$incomeAccount->id,
                        'debit'=>0,
                        'credit'=>$charge->amount,
                        'description'=>'Charge income',
                    ],
                ],
            ]);

            $charge->update([
                'finance_transaction_id'=>$journal->id,
            ]);

            return $charge->fresh([
                'member.user',
                'incomeAccount',
                'financeTransaction.entries.account',
            ]);
        });
    }

    public function pay(MemberCharge $charge,array $data,int $userId): ChargePayment
    {
        return DB::transaction(function()use($charge,$data,$userId){
            $charge=MemberCharge::query()
                ->whereKey($charge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($charge->status,['paid','waived','cancelled'],true)){
                throw ValidationException::withMessages([
                    'charge'=>['This charge cannot receive payment.'],
                ]);
            }

            $amount=round((float)$data['amount'],2);
            $outstanding=round((float)$charge->amount-(float)$charge->paid_amount,2);

            if($amount<=0){
                throw ValidationException::withMessages([
                    'amount'=>['Payment amount must be greater than zero.'],
                ]);
            }

            if($amount>$outstanding){
                throw ValidationException::withMessages([
                    'amount'=>['Payment amount exceeds the outstanding balance.'],
                ]);
            }

            $receiveAccount=Account::query()
                ->whereKey($data['receive_account_id'])
                ->whereIn('sub_type',['cash','bank'])
                ->where('is_active',true)
                ->first();

            if(!$receiveAccount){
                throw ValidationException::withMessages([
                    'receive_account_id'=>['Receive account must be an active Cash or Bank account.'],
                ]);
            }

            $receivable=$this->accounting->account('receivable');

            $payment=ChargePayment::create([
                'payment_no'=>$this->generatePaymentNumber(),
                'member_charge_id'=>$charge->id,
                'amount'=>$amount,
                'receive_account_id'=>$receiveAccount->id,
                'payment_date'=>$data['payment_date'],
                'payment_method'=>$data['payment_method']??null,
                'reference'=>$data['reference']??null,
                'description'=>$data['description']??null,
                'status'=>'posted',
                'created_by'=>$userId,
            ]);

            $journal=$this->accounting->post([
                'transaction_date'=>$payment->payment_date->toDateString(),
                'type'=>'charge_payment',
                'source_module'=>'charge_payment',
                'source_id'=>$payment->id,
                'reference_type'=>ChargePayment::class,
                'reference_id'=>$payment->id,
                'description'=>$payment->description??"Charge payment {$payment->payment_no}",
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

            $newPaid=round((float)$charge->paid_amount+$amount,2);

            $charge->update([
                'paid_amount'=>$newPaid,
                'status'=>$newPaid>=(float)$charge->amount?'paid':'partial',
            ]);

            return $payment->fresh([
                'charge.member.user',
                'receiveAccount',
                'financeTransaction.entries.account',
            ]);
        });
    }

    public function cancel(MemberCharge $charge,string $reason,int $userId): MemberCharge
    {
        return DB::transaction(function()use($charge,$reason,$userId){
            $charge=MemberCharge::query()
                ->whereKey($charge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($charge->status==='cancelled'){
                throw ValidationException::withMessages([
                    'charge'=>['Charge is already cancelled.'],
                ]);
            }

            if((float)$charge->paid_amount>0){
                throw ValidationException::withMessages([
                    'charge'=>['A charge with payments cannot be cancelled directly.'],
                ]);
            }

            $charge->loadMissing('financeTransaction.entries');

            if($charge->financeTransaction){
                $entries=$charge->financeTransaction->entries->map(fn($entry)=>[
                    'account_id'=>$entry->account_id,
                    'debit'=>$entry->credit,
                    'credit'=>$entry->debit,
                    'description'=>"Reversal of {$charge->charge_no}",
                ])->all();

                $this->accounting->post([
                    'transaction_date'=>now()->toDateString(),
                    'type'=>'member_charge_reversal',
                    'source_module'=>'member_charge',
                    'source_id'=>$charge->id,
                    'reference_type'=>MemberCharge::class,
                    'reference_id'=>$charge->id,
                    'description'=>"Cancellation of {$charge->charge_no}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$entries,
                ]);
            }

            $charge->update([
                'status'=>'cancelled',
            ]);

            return $charge->fresh();
        });
    }

    public function waive(MemberCharge $charge,string $reason,int $userId): MemberCharge
    {
        return DB::transaction(function()use($charge,$reason,$userId){
            $charge=MemberCharge::query()
                ->whereKey($charge->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($charge->status,['paid','waived','cancelled'],true)){
                throw ValidationException::withMessages([
                    'charge'=>['This charge cannot be waived.'],
                ]);
            }

            if((float)$charge->paid_amount>0){
                throw ValidationException::withMessages([
                    'charge'=>['Partially paid charges cannot be fully waived.'],
                ]);
            }

            $charge->loadMissing('financeTransaction.entries');

            if($charge->financeTransaction){
                $entries=$charge->financeTransaction->entries->map(fn($entry)=>[
                    'account_id'=>$entry->account_id,
                    'debit'=>$entry->credit,
                    'credit'=>$entry->debit,
                    'description'=>"Waiver of {$charge->charge_no}",
                ])->all();

                $this->accounting->post([
                    'transaction_date'=>now()->toDateString(),
                    'type'=>'member_charge_waiver',
                    'source_module'=>'member_charge',
                    'source_id'=>$charge->id,
                    'reference_type'=>MemberCharge::class,
                    'reference_id'=>$charge->id,
                    'description'=>"Waiver of {$charge->charge_no}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$entries,
                ]);
            }

            $charge->update([
                'status'=>'waived',
            ]);

            return $charge->fresh();
        });
    }

    private function generateChargeNumber(): string
    {
        $prefix='CHG-'.now()->format('Ym').'-';

        $last=MemberCharge::query()
            ->where('charge_no','like',$prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('charge_no');

        $next=$last?((int)substr($last,-6))+1:1;

        return $prefix.str_pad($next,6,'0',STR_PAD_LEFT);
    }

    private function generatePaymentNumber(): string
    {
        $prefix='CPY-'.now()->format('Ym').'-';

        $last=ChargePayment::query()
            ->where('payment_no','like',$prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('payment_no');

        $next=$last?((int)substr($last,-6))+1:1;

        return $prefix.str_pad($next,6,'0',STR_PAD_LEFT);
    }
}