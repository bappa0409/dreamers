<?php

namespace App\Services;

use App\Models\Income;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class IncomeService
{
    public function __construct(
        protected AccountingService $accounting
    ){}

    public function create(array $data,int $userId): Income
    {
        return DB::transaction(function()use($data,$userId){
            $incomeAccount=\App\Models\Account::query()
                ->whereKey($data['income_account_id'])
                ->where('type','income')
                ->where('is_active',true)
                ->first();

            if(!$incomeAccount){
                throw ValidationException::withMessages([
                    'income_account_id'=>['Invalid income account.'],
                ]);
            }

            $receiveAccount=\App\Models\Account::query()
                ->whereKey($data['receive_account_id'])
                ->whereIn('sub_type',['cash','bank'])
                ->where('is_active',true)
                ->first();

            if(!$receiveAccount){
                throw ValidationException::withMessages([
                    'receive_account_id'=>['Receive account must be an active Cash or Bank account.'],
                ]);
            }

            $income=Income::create([
                'income_no'=>$this->generateNumber(),
                'member_id'=>$data['member_id']??null,
                'income_account_id'=>$incomeAccount->id,
                'receive_account_id'=>$receiveAccount->id,
                'amount'=>$data['amount'],
                'income_date'=>$data['income_date'],
                'reference'=>$data['reference']??null,
                'description'=>$data['description']??null,
                'attachment'=>$data['attachment']??null,
                'created_by'=>$userId,
                'status'=>'posted',
            ]);

            $journal=$this->accounting->post([
                'transaction_date'=>$income->income_date->toDateString(),
                'type'=>'income',
                'source_module'=>'income',
                'source_id'=>$income->id,
                'reference_type'=>Income::class,
                'reference_id'=>$income->id,
                'description'=>$income->description??"Income {$income->income_no}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$receiveAccount->id,
                        'debit'=>$income->amount,
                        'credit'=>0,
                        'description'=>'Income received',
                    ],
                    [
                        'account_id'=>$incomeAccount->id,
                        'debit'=>0,
                        'credit'=>$income->amount,
                        'description'=>'Income recognized',
                    ],
                ],
            ]);

            $income->update([
                'finance_transaction_id'=>$journal->id,
            ]);

            return $income->fresh([
                'member.user',
                'incomeAccount',
                'receiveAccount',
                'financeTransaction.entries.account',
                'creator',
            ]);
        });
    }

    public function cancel(Income $income,string $reason,int $userId): Income
    {
        return DB::transaction(function()use($income,$reason,$userId){
            $income=Income::query()
                ->whereKey($income->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($income->status==='cancelled'){
                throw ValidationException::withMessages([
                    'income'=>['Income is already cancelled.'],
                ]);
            }

            $income->loadMissing('financeTransaction.entries');

            if($income->financeTransaction){
                $reversalEntries=$income->financeTransaction->entries->map(fn($entry)=>[
                    'account_id'=>$entry->account_id,
                    'debit'=>$entry->credit,
                    'credit'=>$entry->debit,
                    'description'=>"Reversal of {$income->income_no}",
                ])->all();

                $this->accounting->post([
                    'transaction_date'=>now()->toDateString(),
                    'type'=>'income_reversal',
                    'source_module'=>'income',
                    'source_id'=>$income->id,
                    'reference_type'=>Income::class,
                    'reference_id'=>$income->id,
                    'description'=>"Cancellation of {$income->income_no}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$reversalEntries,
                ]);
            }

            $income->update([
                'status'=>'cancelled',
            ]);

            return $income->fresh();
        });
    }

    private function generateNumber(): string
    {
        $prefix='INC-'.now()->format('Ym').'-';

        $last=Income::query()
            ->where('income_no','like',$prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('income_no');

        $next=$last?((int)substr($last,-6))+1:1;

        return $prefix.str_pad($next,6,'0',STR_PAD_LEFT);
    }
}