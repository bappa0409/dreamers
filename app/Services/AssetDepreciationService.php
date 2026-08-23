<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetDepreciationService
{
    public function __construct(
        protected AccountingService $accounting
    ){}

    public function post(
        Asset $asset,
        array $data,
        ?int $userId=null
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

            $this->ensureDepreciable($asset);

            $date=Carbon::parse(
                $data['depreciation_date']
            )->startOfDay();

            if(
                $date->lt(
                    $asset->purchase_date->copy()->startOfDay()
                )
            ){
                throw ValidationException::withMessages([
                    'depreciation_date'=>[
                        'Depreciation date cannot be earlier than the asset purchase date.'
                    ],
                ]);
            }

            $periodKey=$date->format('Y-m');

            $existing=AssetDepreciation::query()
                ->where('asset_id',$asset->id)
                ->where('period_key',$periodKey)
                ->exists();

            if($existing){
                throw ValidationException::withMessages([
                    'depreciation_date'=>[
                        'Depreciation has already been posted for this month.'
                    ],
                ]);
            }

            $purchaseCost=round(
                (float)$asset->purchase_cost,
                2
            );

            $salvageValue=round(
                (float)$asset->salvage_value,
                2
            );

            $accumulated=round(
                (float)$asset->accumulated_depreciation,
                2
            );

            $depreciableAmount=max(
                round(
                    $purchaseCost-$salvageValue,
                    2
                ),
                0
            );

            $remainingDepreciable=max(
                round(
                    $depreciableAmount-$accumulated,
                    2
                ),
                0
            );

            if($remainingDepreciable<=0){
                throw ValidationException::withMessages([
                    'asset'=>[
                        'This asset is already fully depreciated.'
                    ],
                ]);
            }

            $monthlyDepreciation=round(
                $depreciableAmount/
                (int)$asset->useful_life_months,
                2
            );

            if($monthlyDepreciation<=0){
                throw ValidationException::withMessages([
                    'asset'=>[
                        'Calculated monthly depreciation must be greater than zero.'
                    ],
                ]);
            }

            $amount=min(
                $monthlyDepreciation,
                $remainingDepreciable
            );

            $amount=round(
                $amount,
                2
            );

            $bookValueBefore=max(
                round(
                    $purchaseCost-$accumulated,
                    2
                ),
                0
            );

            $bookValueAfter=max(
                round(
                    $bookValueBefore-$amount,
                    2
                ),
                $salvageValue
            );

            /*
             * Final-period rounding protection.
             */
            if(
                $bookValueBefore-$amount<
                $salvageValue
            ){
                $amount=round(
                    $bookValueBefore-$salvageValue,
                    2
                );

                $bookValueAfter=$salvageValue;
            }

            if($amount<=0){
                throw ValidationException::withMessages([
                    'asset'=>[
                        'No depreciable balance remains.'
                    ],
                ]);
            }

            try{
                $depreciation=AssetDepreciation::create([
                    'asset_id'=>$asset->id,
                    'depreciation_date'=>$date->toDateString(),
                    'amount'=>$amount,
                    'book_value_before'=>$bookValueBefore,
                    'book_value_after'=>$bookValueAfter,
                    'period_key'=>$periodKey,
                    'description'=>$this->nullableString(
                        $data['description']??null
                    )??(
                        "Depreciation {$periodKey} - {$asset->name}"
                    ),
                    'created_by'=>$userId,
                ]);
            }catch(QueryException $exception){
                if(
                    $this->isDuplicateKey(
                        $exception
                    )
                ){
                    throw ValidationException::withMessages([
                        'depreciation_date'=>[
                            'Depreciation has already been posted for this month.'
                        ],
                    ]);
                }

                throw $exception;
            }

            $transaction=$this->postAccountingEntry(
                $asset,
                $depreciation,
                $userId
            );

            $depreciation->update([
                'finance_transaction_id'=>$transaction->id,
            ]);

            $newAccumulated=min(
                round(
                    $accumulated+$amount,
                    2
                ),
                $depreciableAmount
            );

            $asset->update([
                'accumulated_depreciation'=>$newAccumulated,
            ]);

            return $depreciation->fresh([
                'asset',
                'creator:id,name',
                'financeTransaction.entries.account',
            ]);
        });
    }

    protected function ensureDepreciable(
        Asset $asset
    ): void{
        if($asset->status!=='active'){
            throw ValidationException::withMessages([
                'asset'=>[
                    'Only active assets can be depreciated.'
                ],
            ]);
        }

        if(
            !$asset->useful_life_months||
            (int)$asset->useful_life_months<=0
        ){
            throw ValidationException::withMessages([
                'useful_life_months'=>[
                    'Useful life is required before depreciation can be posted.'
                ],
            ]);
        }

        if(
            (float)$asset->purchase_cost<=0
        ){
            throw ValidationException::withMessages([
                'purchase_cost'=>[
                    'Purchase cost must be greater than zero.'
                ],
            ]);
        }

        if(
            (float)$asset->salvage_value<0
        ){
            throw ValidationException::withMessages([
                'salvage_value'=>[
                    'Salvage value cannot be negative.'
                ],
            ]);
        }

        if(
            (float)$asset->salvage_value>
            (float)$asset->purchase_cost
        ){
            throw ValidationException::withMessages([
                'salvage_value'=>[
                    'Salvage value cannot exceed purchase cost.'
                ],
            ]);
        }
    }

    protected function postAccountingEntry(
        Asset $asset,
        AssetDepreciation $depreciation,
        ?int $userId
    ){
        $depreciationExpense=
            $this->accounting->account(
                'depreciation_expense'
            );

        $accumulatedDepreciation=
            $this->accounting->account(
                'accumulated_depreciation'
            );

        return $this->accounting->post([
            'idempotency_key'=>
                "asset:depreciation:{$asset->id}:{$depreciation->period_key}",

            'transaction_date'=>$depreciation
                ->depreciation_date
                ->toDateString(),

            'type'=>'asset_depreciation',

            'source_module'=>'asset_depreciation',

            'source_id'=>$depreciation->id,

            'reference_type'=>AssetDepreciation::class,

            'reference_id'=>$depreciation->id,

            'description'=>$depreciation->description
                ??"Depreciation {$asset->asset_code} {$depreciation->period_key}",

            'user_id'=>$userId,

            'entries'=>[
                [
                    'account_id'=>$depreciationExpense->id,
                    'debit'=>(float)$depreciation->amount,
                    'credit'=>0,
                    'description'=>
                        "Depreciation expense - {$asset->asset_code}",
                ],
                [
                    'account_id'=>$accumulatedDepreciation->id,
                    'debit'=>0,
                    'credit'=>(float)$depreciation->amount,
                    'description'=>
                        "Accumulated depreciation - {$asset->asset_code}",
                ],
            ],
        ]);
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

    protected function isDuplicateKey(
        QueryException $exception
    ): bool{
        return in_array(
            (string)$exception->getCode(),
            [
                '23000',
                '23505',
            ],
            true
        );
    }
}