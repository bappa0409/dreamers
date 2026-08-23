<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\AssetDepreciation;

class AssetService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected NumberSequenceService $numberSequenceService
    ){}

    public function create(
        array $data,
        int $userId
    ): Asset{
        return DB::transaction(function()use(
            $data,
            $userId
        ){
            $assetAccount=$this->assetAccount(
                (int)$data['asset_account_id']
            );

            $paymentAccount=$this->cashBankAccount(
                (int)$data['payment_account_id'],
                'payment_account_id'
            );

            $purchaseCost=round(
                (float)$data['purchase_cost'],
                2
            );

            $salvageValue=round(
                (float)($data['salvage_value']??0),
                2
            );

            if($salvageValue>$purchaseCost){
                throw ValidationException::withMessages([
                    'salvage_value'=>[
                        'Salvage value cannot exceed purchase cost.'
                    ],
                ]);
            }

            $asset=Asset::create([
                'asset_code'=>$this->generateNumber(),
                'name'=>trim($data['name']),
                'category'=>$this->nullableString(
                    $data['category']??null
                ),
                'asset_account_id'=>$assetAccount->id,
                'payment_account_id'=>$paymentAccount->id,
                'purchase_cost'=>$purchaseCost,
                'purchase_date'=>$data['purchase_date'],
                'vendor'=>$this->nullableString(
                    $data['vendor']??null
                ),
                'reference'=>$this->nullableString(
                    $data['reference']??null
                ),
                'location'=>$this->nullableString(
                    $data['location']??null
                ),
                'serial_no'=>$this->nullableString(
                    $data['serial_no']??null
                ),
                'attachment'=>$data['attachment']??null,
                'useful_life_months'=>$data['useful_life_months']??null,
                'salvage_value'=>$salvageValue,
                'accumulated_depreciation'=>0,
                'status'=>'active',
                'created_by'=>$userId,
                'description'=>$this->nullableString(
                    $data['description']??null
                ),
            ]);

            $journal=$this->accountingService->post([
                'idempotency_key'=>"asset:purchase:{$asset->id}",
                'transaction_date'=>$asset
                    ->purchase_date
                    ->toDateString(),
                'type'=>'asset_purchase',
                'source_module'=>'asset',
                'source_id'=>$asset->id,
                'reference_type'=>Asset::class,
                'reference_id'=>$asset->id,
                'description'=>$asset->description
                    ??"Asset purchase {$asset->asset_code}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$assetAccount->id,
                        'debit'=>$purchaseCost,
                        'credit'=>0,
                        'description'=>'Asset acquisition',
                    ],
                    [
                        'account_id'=>$paymentAccount->id,
                        'debit'=>0,
                        'credit'=>$purchaseCost,
                        'description'=>'Asset purchase payment',
                    ],
                ],
            ]);

            $asset->update([
                'finance_transaction_id'=>$journal->id,
            ]);

            return $this->freshAsset($asset);
        });
    }

    public function update(
        Asset $asset,
        array $data
    ): Asset{
        return DB::transaction(function()use(
            $asset,
            $data
        ){
            $asset=Asset::query()
                ->whereKey($asset->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($asset->status!=='active'){
                throw ValidationException::withMessages([
                    'asset'=>[
                        'Only active assets can be updated.'
                    ],
                ]);
            }

            if($asset->finance_transaction_id){
                foreach([
                    'asset_account_id',
                    'payment_account_id',
                    'purchase_cost',
                    'purchase_date',
                ] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->changed(
                            $asset->{$field},
                            $data[$field]
                        )
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Posted purchase data cannot be changed. Cancel the asset and create a new record instead.'
                            ],
                        ]);
                    }
                }
            }

            if(
                (float)$asset->accumulated_depreciation>0
            ){
                foreach([
                    'useful_life_months',
                    'salvage_value',
                ] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->changed(
                            $asset->{$field},
                            $data[$field]
                        )
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Depreciation settings cannot be changed after depreciation has been recorded.'
                            ],
                        ]);
                    }
                }
            }

            if(
                array_key_exists(
                    'asset_account_id',
                    $data
                )
            ){
                $data['asset_account_id']=$this
                    ->assetAccount(
                        (int)$data['asset_account_id']
                    )
                    ->id;
            }

            if(
                array_key_exists(
                    'payment_account_id',
                    $data
                )
            ){
                $data['payment_account_id']=$this
                    ->cashBankAccount(
                        (int)$data['payment_account_id'],
                        'payment_account_id'
                    )
                    ->id;
            }

            $purchaseCost=round(
                (float)(
                    $data['purchase_cost']
                    ??$asset->purchase_cost
                ),
                2
            );

            $salvageValue=round(
                (float)(
                    array_key_exists(
                        'salvage_value',
                        $data
                    )
                        ?($data['salvage_value']??0)
                        :$asset->salvage_value
                ),
                2
            );

            if($salvageValue>$purchaseCost){
                throw ValidationException::withMessages([
                    'salvage_value'=>[
                        'Salvage value cannot exceed purchase cost.'
                    ],
                ]);
            }

            $clean=[];

            foreach([
                'asset_account_id',
                'payment_account_id',
                'purchase_cost',
                'purchase_date',
                'name',
                'category',
                'vendor',
                'reference',
                'location',
                'serial_no',
                'attachment',
                'useful_life_months',
                'salvage_value',
                'description',
            ] as $field){
                if(array_key_exists($field,$data)){
                    $clean[$field]=$data[$field];
                }
            }

            foreach([
                'name',
                'category',
                'vendor',
                'reference',
                'location',
                'serial_no',
                'description',
            ] as $field){
                if(array_key_exists($field,$clean)){
                    $clean[$field]=$this->nullableString(
                        $clean[$field]
                    );
                }
            }

            if(array_key_exists('name',$clean)){
                if(!$clean['name']){
                    throw ValidationException::withMessages([
                        'name'=>[
                            'Asset name is required.'
                        ],
                    ]);
                }
            }

            if(array_key_exists('purchase_cost',$clean)){
                $clean['purchase_cost']=$purchaseCost;
            }

            if(array_key_exists('salvage_value',$clean)){
                $clean['salvage_value']=$salvageValue;
            }

            $asset->update($clean);

            return $this->freshAsset($asset);
        });
    }

   public function sell(
    Asset $asset,
    array $data,
    int $userId
): Asset{
    return DB::transaction(function()use(
        $asset,
        $data,
        $userId
    ){
        $asset=Asset::query()
            ->whereKey($asset->id)
            ->lockForUpdate()
            ->firstOrFail();

        $this->ensureActiveAsset($asset);

        if($asset->disposal_transaction_id){
            throw ValidationException::withMessages([
                'asset'=>[
                    'Asset already has a disposal transaction.'
                ],
            ]);
        }

        $this->validateDisposalDate(
            $asset,
            $data['disposal_date']
        );

        $receiveAccount=$this->cashBankAccount(
            (int)$data['receive_account_id'],
            'receive_account_id'
        );

        $assetAccount=$this->assetAccount(
            (int)$asset->asset_account_id
        );

        $accumulatedDepreciationAccount=null;

        $accumulatedDepreciation=round(
            (float)$asset->accumulated_depreciation,
            2
        );

        if($accumulatedDepreciation>0){
            $accumulatedDepreciationAccount=
                $this->accountingService->account(
                    'accumulated_depreciation'
                );
        }

        $purchaseCost=round(
            (float)$asset->purchase_cost,
            2
        );

        $bookValue=round(
            $purchaseCost-$accumulatedDepreciation,
            2
        );

        $bookValue=max(
            $bookValue,
            0
        );

        $saleAmount=round(
            (float)$data['amount'],
            2
        );

        if($saleAmount<0){
            throw ValidationException::withMessages([
                'amount'=>[
                    'Sale amount cannot be negative.'
                ],
            ]);
        }

        $entries=[
            [
                'account_id'=>$receiveAccount->id,
                'debit'=>$saleAmount,
                'credit'=>0,
                'description'=>'Asset sale proceeds',
            ],
        ];

        if(
            $accumulatedDepreciation>0&&
            $accumulatedDepreciationAccount
        ){
            $entries[]=[
                'account_id'=>
                    $accumulatedDepreciationAccount->id,
                'debit'=>$accumulatedDepreciation,
                'credit'=>0,
                'description'=>
                    'Reverse accumulated depreciation on asset disposal',
            ];
        }

        $entries[]=[
            'account_id'=>$assetAccount->id,
            'debit'=>0,
            'credit'=>$purchaseCost,
            'description'=>'Asset derecognition',
        ];

        if($saleAmount>$bookValue){
            $gainAccount=$this
                ->accountingService
                ->account(
                    'asset_disposal_gain'
                );

            $entries[]=[
                'account_id'=>$gainAccount->id,
                'debit'=>0,
                'credit'=>round(
                    $saleAmount-$bookValue,
                    2
                ),
                'description'=>'Gain on asset disposal',
            ];
        }

        if($saleAmount<$bookValue){
            $lossAccount=$this
                ->accountingService
                ->account(
                    'asset_disposal_loss'
                );

            $entries[]=[
                'account_id'=>$lossAccount->id,
                'debit'=>round(
                    $bookValue-$saleAmount,
                    2
                ),
                'credit'=>0,
                'description'=>'Loss on asset disposal',
            ];
        }

        $journal=$this->accountingService->post([
            'idempotency_key'=>"asset:sale:{$asset->id}",
            'transaction_date'=>$data['disposal_date'],
            'type'=>'asset_sale',
            'source_module'=>'asset',
            'source_id'=>$asset->id,
            'reference_type'=>Asset::class,
            'reference_id'=>$asset->id,
            'description'=>$this->nullableString(
                $data['note']??null
            )??"Asset sale {$asset->asset_code}",
            'user_id'=>$userId,
            'entries'=>$entries,
        ]);

        $asset->update([
            'status'=>'sold',
            'disposal_date'=>$data['disposal_date'],
            'disposal_amount'=>$saleAmount,
            'disposal_note'=>$this->nullableString(
                $data['note']??null
            ),
            'disposal_transaction_id'=>$journal->id,
        ]);

        return $this->freshAsset($asset);
    });
}

    public function dispose(
    Asset $asset,
    array $data,
    int $userId
): Asset{
    return DB::transaction(function()use(
        $asset,
        $data,
        $userId
    ){
        $asset=Asset::query()
            ->whereKey($asset->id)
            ->lockForUpdate()
            ->firstOrFail();

        $this->ensureActiveAsset($asset);

        if($asset->disposal_transaction_id){
            throw ValidationException::withMessages([
                'asset'=>[
                    'Asset already has a disposal transaction.'
                ],
            ]);
        }

        $this->validateDisposalDate(
            $asset,
            $data['disposal_date']
        );

        $assetAccount=$this->assetAccount(
            (int)$asset->asset_account_id
        );

        $purchaseCost=round(
            (float)$asset->purchase_cost,
            2
        );

        $accumulatedDepreciation=round(
            (float)$asset->accumulated_depreciation,
            2
        );

        $bookValue=max(
            round(
                $purchaseCost-$accumulatedDepreciation,
                2
            ),
            0
        );

        $entries=[];

        if($accumulatedDepreciation>0){
            $accumulatedDepreciationAccount=
                $this->accountingService->account(
                    'accumulated_depreciation'
                );

            $entries[]=[
                'account_id'=>
                    $accumulatedDepreciationAccount->id,
                'debit'=>$accumulatedDepreciation,
                'credit'=>0,
                'description'=>
                    'Reverse accumulated depreciation',
            ];
        }

        if($bookValue>0){
            $lossAccount=$this
                ->accountingService
                ->account(
                    'asset_disposal_loss'
                );

            $entries[]=[
                'account_id'=>$lossAccount->id,
                'debit'=>$bookValue,
                'credit'=>0,
                'description'=>'Loss on asset disposal',
            ];
        }

        $entries[]=[
            'account_id'=>$assetAccount->id,
            'debit'=>0,
            'credit'=>$purchaseCost,
            'description'=>'Asset derecognition',
        ];

        $journal=$this->accountingService->post([
            'idempotency_key'=>
                "asset:disposal:{$asset->id}",
            'transaction_date'=>$data['disposal_date'],
            'type'=>'asset_disposal',
            'source_module'=>'asset',
            'source_id'=>$asset->id,
            'reference_type'=>Asset::class,
            'reference_id'=>$asset->id,
            'description'=>$this->nullableString(
                $data['note']??null
            )??"Asset disposal {$asset->asset_code}",
            'user_id'=>$userId,
            'entries'=>$entries,
        ]);

        $asset->update([
            'status'=>'disposed',
            'disposal_date'=>$data['disposal_date'],
            'disposal_amount'=>0,
            'disposal_note'=>$this->nullableString(
                $data['note']??null
            ),
            'disposal_transaction_id'=>$journal->id,
        ]);

        return $this->freshAsset($asset);
    });
}

    public function cancel(
        Asset $asset,
        string $reason,
        int $userId
    ): Asset{
        return DB::transaction(function()use(
            $asset,
            $reason,
            $userId
        ){
            $asset=Asset::query()
                ->whereKey($asset->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureActiveAsset($asset);

            if($asset->disposal_transaction_id){
                throw ValidationException::withMessages([
                    'asset'=>[
                        'Disposed or sold asset cannot be cancelled.'
                    ],
                ]);
            }

            if(
                (float)$asset->accumulated_depreciation>0
            ){
                throw ValidationException::withMessages([
                    'asset'=>[
                        'Asset with depreciation cannot be cancelled directly.'
                    ],
                ]);
            }

            $asset->loadMissing(
                'financeTransaction.entries'
            );

            if($asset->financeTransaction){
                $entries=$asset
                    ->financeTransaction
                    ->entries
                    ->map(fn($entry)=>[
                        'account_id'=>$entry->account_id,
                        'debit'=>(float)$entry->credit,
                        'credit'=>(float)$entry->debit,
                        'description'=>"Reversal of {$asset->asset_code}",
                    ])
                    ->all();

                $this->accountingService->post([
                    'idempotency_key'=>"asset:cancel:{$asset->id}",
                    'transaction_date'=>now()->toDateString(),
                    'type'=>'asset_purchase_reversal',
                    'source_module'=>'asset',
                    'source_id'=>$asset->id,
                    'reference_type'=>Asset::class,
                    'reference_id'=>$asset->id,
                    'description'=>
                        "Cancellation of {$asset->asset_code}: ".
                        trim($reason),
                    'user_id'=>$userId,
                    'entries'=>$entries,
                ]);
            }

            $asset->update([
                'status'=>'cancelled',
                'disposal_note'=>trim($reason),
            ]);

            return $this->freshAsset($asset);
        });
    }

    public function depreciate(
    Asset $asset,
    array $data,
    int $userId
): AssetDepreciation{
    return DB::transaction(function()use(
        $asset,
        $data,
        $userId
    ){
        $asset=Asset::query()
            ->whereKey($asset->id)
            ->lockForUpdate()
            ->firstOrFail();

        $this->ensureActiveAsset($asset);

        if(
            !$asset->useful_life_months||
            $asset->useful_life_months<=0
        ){
            throw ValidationException::withMessages([
                'asset'=>[
                    'Useful life is required before depreciation can be recorded.'
                ],
            ]);
        }

        $date=$data['depreciation_date'];

        if(
            $date<
            $asset->purchase_date->toDateString()
        ){
            throw ValidationException::withMessages([
                'depreciation_date'=>[
                    'Depreciation date cannot be earlier than purchase date.'
                ],
            ]);
        }

        if(
            $asset->disposal_date&&
            $date>$asset->disposal_date->toDateString()
        ){
            throw ValidationException::withMessages([
                'depreciation_date'=>[
                    'Depreciation cannot be recorded after disposal date.'
                ],
            ]);
        }

        $existing=AssetDepreciation::query()
            ->where('asset_id',$asset->id)
            ->whereYear('depreciation_date',date('Y',strtotime($date)))
            ->whereMonth('depreciation_date',date('m',strtotime($date)))
            ->exists();

        if($existing){
            throw ValidationException::withMessages([
                'depreciation_date'=>[
                    'Depreciation has already been recorded for this month.'
                ],
            ]);
        }

        $remaining=round(
            max(
                (float)$asset->purchase_cost-
                (float)$asset->salvage_value-
                (float)$asset->accumulated_depreciation,
                0
            ),
            2
        );

        if($remaining<=0){
            throw ValidationException::withMessages([
                'asset'=>[
                    'Asset is already fully depreciated.'
                ],
            ]);
        }

        $monthly=round(
            (
                (float)$asset->purchase_cost-
                (float)$asset->salvage_value
            )/$asset->useful_life_months,
            2
        );

        $amount=isset($data['amount'])
            ?round((float)$data['amount'],2)
            :$monthly;

        if($amount<=0){
            throw ValidationException::withMessages([
                'amount'=>[
                    'Depreciation amount must be greater than zero.'
                ],
            ]);
        }

        $amount=min(
            $amount,
            $remaining
        );

        $expenseAccount=$this
            ->accountingService
            ->account('depreciation_expense');

        $accumulatedAccount=$this
            ->accountingService
            ->account('accumulated_depreciation');

        $depreciation=AssetDepreciation::create([
            'asset_id'=>$asset->id,
            'depreciation_date'=>$date,
            'amount'=>$amount,
            'description'=>$this->nullableString(
                $data['description']??null
            ),
            'created_by'=>$userId,
        ]);

        $journal=$this->accountingService->post([
            'idempotency_key'=>
                "asset:depreciation:{$asset->id}:{$date}",
            'transaction_date'=>$date,
            'type'=>'asset_depreciation',
            'source_module'=>'asset',
            'source_id'=>$asset->id,
            'reference_type'=>AssetDepreciation::class,
            'reference_id'=>$depreciation->id,
            'description'=>$depreciation->description
                ??"Depreciation {$asset->asset_code}",
            'user_id'=>$userId,
            'entries'=>[
                [
                    'account_id'=>$expenseAccount->id,
                    'debit'=>$amount,
                    'credit'=>0,
                    'description'=>
                        "Depreciation expense {$asset->asset_code}",
                ],
                [
                    'account_id'=>$accumulatedAccount->id,
                    'debit'=>0,
                    'credit'=>$amount,
                    'description'=>
                        "Accumulated depreciation {$asset->asset_code}",
                ],
            ],
        ]);

        $depreciation->update([
            'finance_transaction_id'=>$journal->id,
        ]);

        $asset->update([
            'accumulated_depreciation'=>round(
                (float)$asset->accumulated_depreciation+
                $amount,
                2
            ),
        ]);

        return $depreciation->fresh([
            'asset',
            'creator',
            'financeTransaction.entries.account',
        ]);
    });
}

    protected function assetAccount(
        int $accountId
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->where('type','asset')
            ->where('sub_type','fixed_asset')
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                'asset_account_id'=>[
                    'Selected account must be an active Fixed Asset posting account.'
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
            ->whereIn(
                'sub_type',
                ['cash','bank']
            )
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

    protected function ensureActiveAsset(
        Asset $asset
    ): void{
        if($asset->status!=='active'){
            throw ValidationException::withMessages([
                'asset'=>[
                    'Only active assets can perform this action.'
                ],
            ]);
        }
    }

    protected function validateDisposalDate(
        Asset $asset,
        string $date
    ): void{
        if(
            $date<
            $asset->purchase_date->toDateString()
        ){
            throw ValidationException::withMessages([
                'disposal_date'=>[
                    'Disposal date cannot be earlier than purchase date.'
                ],
            ]);
        }
    }

    protected function changed(
        mixed $current,
        mixed $new
    ): bool{
        if($current instanceof \DateTimeInterface){
            return $current->format('Y-m-d')
                !==(string)$new;
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

        $value=trim((string)$value);

        return $value===''
            ?null
            :$value;
    }

    protected function freshAsset(
        Asset $asset
    ): Asset{
        return $asset->fresh([
            'assetAccount',
            'paymentAccount',
            'financeTransaction.entries.account',
            'disposalTransaction.entries.account',
            'creator',
        ]);
    }

    protected function generateNumber(): string
    {
        $month=now()->format('Ym');
        $prefix="AST-{$month}-";

        return $this->numberSequenceService->next(
            key:"asset:{$month}",
            prefix:$prefix,
            digits:6,
            initialValue:function()use($prefix){
                $last=Asset::query()
                    ->where(
                        'asset_code',
                        'like',
                        $prefix.'%'
                    )
                    ->orderByDesc('id')
                    ->value('asset_code');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }
}