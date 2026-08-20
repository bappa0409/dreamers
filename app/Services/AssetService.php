<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetService
{
    public function __construct(
        protected AccountingService $accounting
    ){}

    public function create(array $data,int $userId): Asset
    {
        return DB::transaction(function()use($data,$userId){
            $assetAccount=Account::query()
                ->whereKey($data['asset_account_id'])
                ->where('type','asset')
                ->where('sub_type','fixed_asset')
                ->where('is_active',true)
                ->first();

            if(!$assetAccount){
                throw ValidationException::withMessages([
                    'asset_account_id'=>['Invalid fixed asset account.'],
                ]);
            }

            $paymentAccount=Account::query()
                ->whereKey($data['payment_account_id'])
                ->whereIn('sub_type',['cash','bank'])
                ->where('is_active',true)
                ->first();

            if(!$paymentAccount){
                throw ValidationException::withMessages([
                    'payment_account_id'=>['Payment account must be an active Cash or Bank account.'],
                ]);
            }

            $asset=Asset::create([
                'asset_code'=>$this->generateNumber(),
                'name'=>$data['name'],
                'category'=>$data['category']??null,
                'asset_account_id'=>$assetAccount->id,
                'payment_account_id'=>$paymentAccount->id,
                'purchase_cost'=>$data['purchase_cost'],
                'purchase_date'=>$data['purchase_date'],
                'vendor'=>$data['vendor']??null,
                'reference'=>$data['reference']??null,
                'location'=>$data['location']??null,
                'serial_no'=>$data['serial_no']??null,
                'attachment'=>$data['attachment']??null,
                'useful_life_months'=>$data['useful_life_months']??null,
                'salvage_value'=>$data['salvage_value']??0,
                'accumulated_depreciation'=>0,
                'status'=>'active',
                'created_by'=>$userId,
                'description'=>$data['description']??null,
            ]);

            $journal=$this->accounting->post([
                'transaction_date'=>$asset->purchase_date->toDateString(),
                'type'=>'asset_purchase',
                'source_module'=>'asset',
                'source_id'=>$asset->id,
                'reference_type'=>Asset::class,
                'reference_id'=>$asset->id,
                'description'=>$asset->description??"Asset purchase {$asset->asset_code}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$assetAccount->id,
                        'debit'=>$asset->purchase_cost,
                        'credit'=>0,
                        'description'=>'Asset acquisition',
                    ],
                    [
                        'account_id'=>$paymentAccount->id,
                        'debit'=>0,
                        'credit'=>$asset->purchase_cost,
                        'description'=>'Asset purchase payment',
                    ],
                ],
            ]);

            $asset->update([
                'finance_transaction_id'=>$journal->id,
            ]);

            return $asset->fresh([
                'assetAccount',
                'paymentAccount',
                'financeTransaction.entries.account',
                'creator',
            ]);
        });
    }

    public function sell(Asset $asset,array $data,int $userId): Asset
    {
        return DB::transaction(function()use($asset,$data,$userId){
            $asset=Asset::query()
                ->whereKey($asset->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($asset->status!=='active'){
                throw ValidationException::withMessages([
                    'asset'=>['Only active assets can be sold.'],
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

            $assetAccount=$asset->assetAccount()->firstOrFail();
            $bookValue=round((float)$asset->book_value,2);
            $saleAmount=round((float)$data['amount'],2);

            if($saleAmount<0){
                throw ValidationException::withMessages([
                    'amount'=>['Sale amount cannot be negative.'],
                ]);
            }

            $entries=[
                [
                    'account_id'=>$receiveAccount->id,
                    'debit'=>$saleAmount,
                    'credit'=>0,
                    'description'=>'Asset sale proceeds',
                ],
                [
                    'account_id'=>$assetAccount->id,
                    'debit'=>0,
                    'credit'=>$bookValue,
                    'description'=>'Asset derecognition',
                ],
            ];

            if($saleAmount>$bookValue){
                $gainAccount=$this->accounting->account('asset_disposal_gain');

                $entries[]=[
                    'account_id'=>$gainAccount->id,
                    'debit'=>0,
                    'credit'=>round($saleAmount-$bookValue,2),
                    'description'=>'Gain on asset disposal',
                ];
            }

            if($saleAmount<$bookValue){
                $lossAccount=$this->accounting->account('asset_disposal_loss');

                $entries[]=[
                    'account_id'=>$lossAccount->id,
                    'debit'=>round($bookValue-$saleAmount,2),
                    'credit'=>0,
                    'description'=>'Loss on asset disposal',
                ];
            }

            $journal=$this->accounting->post([
                'transaction_date'=>$data['disposal_date'],
                'type'=>'asset_sale',
                'source_module'=>'asset',
                'source_id'=>$asset->id,
                'reference_type'=>Asset::class,
                'reference_id'=>$asset->id,
                'description'=>$data['note']??"Asset sale {$asset->asset_code}",
                'user_id'=>$userId,
                'entries'=>$entries,
            ]);

            $asset->update([
                'status'=>'sold',
                'disposal_date'=>$data['disposal_date'],
                'disposal_amount'=>$saleAmount,
                'disposal_note'=>$data['note']??null,
                'disposal_transaction_id'=>$journal->id,
            ]);

            return $asset->fresh([
                'assetAccount',
                'paymentAccount',
                'disposalTransaction.entries.account',
            ]);
        });
    }

    public function dispose(Asset $asset,array $data,int $userId): Asset
    {
        return DB::transaction(function()use($asset,$data,$userId){
            $asset=Asset::query()
                ->whereKey($asset->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($asset->status!=='active'){
                throw ValidationException::withMessages([
                    'asset'=>['Only active assets can be disposed.'],
                ]);
            }

            $bookValue=round((float)$asset->book_value,2);
            $assetAccount=$asset->assetAccount()->firstOrFail();

            $entries=[
                [
                    'account_id'=>$assetAccount->id,
                    'debit'=>0,
                    'credit'=>$bookValue,
                    'description'=>'Asset derecognition',
                ],
            ];

            if($bookValue>0){
                $lossAccount=$this->accounting->account('asset_disposal_loss');

                $entries[]=[
                    'account_id'=>$lossAccount->id,
                    'debit'=>$bookValue,
                    'credit'=>0,
                    'description'=>'Loss on asset disposal',
                ];
            }

            $journal=$this->accounting->post([
                'transaction_date'=>$data['disposal_date'],
                'type'=>'asset_disposal',
                'source_module'=>'asset',
                'source_id'=>$asset->id,
                'reference_type'=>Asset::class,
                'reference_id'=>$asset->id,
                'description'=>$data['note']??"Asset disposal {$asset->asset_code}",
                'user_id'=>$userId,
                'entries'=>$entries,
            ]);

            $asset->update([
                'status'=>'disposed',
                'disposal_date'=>$data['disposal_date'],
                'disposal_amount'=>0,
                'disposal_note'=>$data['note']??null,
                'disposal_transaction_id'=>$journal->id,
            ]);

            return $asset->fresh([
                'assetAccount',
                'disposalTransaction.entries.account',
            ]);
        });
    }

    public function cancel(Asset $asset,string $reason,int $userId): Asset
    {
        return DB::transaction(function()use($asset,$reason,$userId){
            $asset=Asset::query()
                ->whereKey($asset->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($asset->status!=='active'){
                throw ValidationException::withMessages([
                    'asset'=>['Only active assets can be cancelled.'],
                ]);
            }

            if((float)$asset->accumulated_depreciation>0){
                throw ValidationException::withMessages([
                    'asset'=>['Asset with depreciation cannot be cancelled directly.'],
                ]);
            }

            $asset->loadMissing('financeTransaction.entries');

            if($asset->financeTransaction){
                $entries=$asset->financeTransaction->entries->map(fn($entry)=>[
                    'account_id'=>$entry->account_id,
                    'debit'=>$entry->credit,
                    'credit'=>$entry->debit,
                    'description'=>"Reversal of {$asset->asset_code}",
                ])->all();

                $this->accounting->post([
                    'transaction_date'=>now()->toDateString(),
                    'type'=>'asset_purchase_reversal',
                    'source_module'=>'asset',
                    'source_id'=>$asset->id,
                    'reference_type'=>Asset::class,
                    'reference_id'=>$asset->id,
                    'description'=>"Cancellation of {$asset->asset_code}: {$reason}",
                    'user_id'=>$userId,
                    'entries'=>$entries,
                ]);
            }

            $asset->update([
                'status'=>'cancelled',
                'disposal_note'=>$reason,
            ]);

            return $asset->fresh();
        });
    }

    private function generateNumber(): string
    {
        $prefix='AST-'.now()->format('Ym').'-';

        $last=Asset::query()
            ->where('asset_code','like',$prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('asset_code');

        $next=$last?((int)substr($last,-6))+1:1;

        return $prefix.str_pad($next,6,'0',STR_PAD_LEFT);
    }
}