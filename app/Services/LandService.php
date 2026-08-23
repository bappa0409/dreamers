<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Land;
use App\Models\LandDisposal;
use App\Models\LandValuation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class LandService
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected AccountingService $accounting
    ){}

    public function create(
        array $data,
        ?int $userId=null
    ): Land{
        return DB::transaction(function()use(
            $data,
            $userId
        ){
            $purchasePrice=round(
                (float)($data['purchase_price']??0),
                2
            );

            $status=$data['status']??'planned';

            if($status==='sold'){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Land cannot be created as sold. Use the land sale process.'
                    ],
                ]);
            }

            $paymentAccount=null;

            if($status==='purchased'){
                if($purchasePrice<=0){
                    throw ValidationException::withMessages([
                        'purchase_price'=>[
                            'Purchase price must be greater than zero for purchased land.'
                        ],
                    ]);
                }

                if(empty($data['purchase_date'])){
                    throw ValidationException::withMessages([
                        'purchase_date'=>[
                            'Purchase date is required for purchased land.'
                        ],
                    ]);
                }

                if(empty($data['payment_account_id'])){
                    throw ValidationException::withMessages([
                        'payment_account_id'=>[
                            'Payment account is required for purchased land.'
                        ],
                    ]);
                }

                $paymentAccount=$this->cashBankAccount(
                    (int)$data['payment_account_id'],
                    'payment_account_id'
                );
            }

            $currentValue=array_key_exists(
                'current_value',
                $data
            )
                ?round((float)$data['current_value'],2)
                :$purchasePrice;

            if($currentValue<0){
                throw ValidationException::withMessages([
                    'current_value'=>[
                        'Current value cannot be negative.'
                    ],
                ]);
            }

            $land=Land::create([
                'land_code'=>$this->generateLandCode(),
                'title'=>trim($data['title']),
                'description'=>$this->clean(
                    $data['description']??null
                ),
                'district'=>$this->clean(
                    $data['district']??null
                ),
                'upazila'=>$this->clean(
                    $data['upazila']??null
                ),
                'mouza'=>$this->clean(
                    $data['mouza']??null
                ),
                'khatian_no'=>$this->clean(
                    $data['khatian_no']??null
                ),
                'dag_no'=>$this->clean(
                    $data['dag_no']??null
                ),
                'land_area'=>$data['land_area']??null,
                'area_unit'=>$data['area_unit']??'decimal',
                'purchase_price'=>$purchasePrice,
                'current_value'=>$currentValue,
                'purchase_date'=>$data['purchase_date']??null,
                'seller_name'=>$this->clean(
                    $data['seller_name']??null
                ),
                'seller_phone'=>$this->clean(
                    $data['seller_phone']??null
                ),
                'deed_no'=>$this->clean(
                    $data['deed_no']??null
                ),
                'registration_no'=>$this->clean(
                    $data['registration_no']??null
                ),
                'payment_account_id'=>$paymentAccount?->id,
                'status'=>$status,
                'notes'=>$this->clean(
                    $data['notes']??null
                ),
                'created_by'=>$userId,
            ]);

            if($status==='purchased'){
                $transaction=$this->postPurchase(
                    $land,
                    $paymentAccount,
                    $userId
                );

                $land->update([
                    'finance_transaction_id'=>$transaction->id,
                ]);
            }

            $this->forgetCachesAfterCommit();

            return $this->freshLand($land);
        });
    }

    public function update(
        Land $land,
        array $data,
        ?int $userId=null
    ): Land{
        return DB::transaction(function()use(
            $land,
            $data,
            $userId
        ){
            $land=Land::query()
                ->whereKey($land->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($land->status==='sold'){
                throw ValidationException::withMessages([
                    'land'=>[
                        'Sold land cannot be edited.'
                    ],
                ]);
            }

            $newStatus=$data['status']??$land->status;

            if($newStatus==='sold'){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Land cannot be marked as sold manually. Use the land sale process.'
                    ],
                ]);
            }

            if(
                $land->finance_transaction_id&&
                $newStatus!=='purchased'
            ){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Accounting-posted purchased land cannot be changed back to another status.'
                    ],
                ]);
            }

            if($land->finance_transaction_id){
                foreach([
                    'purchase_price',
                    'purchase_date',
                    'payment_account_id',
                ] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->valueChanged(
                            $land->{$field},
                            $data[$field]
                        )
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Posted purchase information cannot be changed.'
                            ],
                        ]);
                    }
                }
            }

            $purchasePrice=round(
                (float)(
                    $data['purchase_price']
                    ??$land->purchase_price
                ),
                2
            );

            $paymentAccount=null;

            if(
                $newStatus==='purchased'&&
                !$land->finance_transaction_id
            ){
                if($purchasePrice<=0){
                    throw ValidationException::withMessages([
                        'purchase_price'=>[
                            'Purchase price must be greater than zero.'
                        ],
                    ]);
                }

                $purchaseDate=
                    $data['purchase_date']
                    ??$land->purchase_date;

                if(!$purchaseDate){
                    throw ValidationException::withMessages([
                        'purchase_date'=>[
                            'Purchase date is required before marking land as purchased.'
                        ],
                    ]);
                }

                $paymentAccountId=
                    $data['payment_account_id']
                    ??$land->payment_account_id;

                if(!$paymentAccountId){
                    throw ValidationException::withMessages([
                        'payment_account_id'=>[
                            'Payment account is required before marking land as purchased.'
                        ],
                    ]);
                }

                $paymentAccount=$this->cashBankAccount(
                    (int)$paymentAccountId,
                    'payment_account_id'
                );
            }

            $clean=[];

            foreach([
                'title',
                'description',
                'district',
                'upazila',
                'mouza',
                'khatian_no',
                'dag_no',
                'land_area',
                'area_unit',
                'purchase_price',
                'current_value',
                'purchase_date',
                'seller_name',
                'seller_phone',
                'deed_no',
                'registration_no',
                'payment_account_id',
                'status',
                'notes',
            ] as $field){
                if(array_key_exists($field,$data)){
                    $clean[$field]=$data[$field];
                }
            }

            foreach([
                'title',
                'description',
                'district',
                'upazila',
                'mouza',
                'khatian_no',
                'dag_no',
                'seller_name',
                'seller_phone',
                'deed_no',
                'registration_no',
                'notes',
            ] as $field){
                if(array_key_exists($field,$clean)){
                    $clean[$field]=$this->clean(
                        $clean[$field]
                    );
                }
            }

            if(array_key_exists('purchase_price',$clean)){
                $clean['purchase_price']=$purchasePrice;
            }

            if(array_key_exists('current_value',$clean)){
                $currentValue=round(
                    (float)$clean['current_value'],
                    2
                );

                if($currentValue<0){
                    throw ValidationException::withMessages([
                        'current_value'=>[
                            'Current value cannot be negative.'
                        ],
                    ]);
                }

                $clean['current_value']=$currentValue;
            }

            if($paymentAccount){
                $clean['payment_account_id']=$paymentAccount->id;
            }

            $land->update($clean);

            if(
                $newStatus==='purchased'&&
                !$land->finance_transaction_id
            ){
                $land->refresh();

                $transaction=$this->postPurchase(
                    $land,
                    $paymentAccount,
                    $userId
                );

                $land->update([
                    'finance_transaction_id'=>$transaction->id,
                ]);
            }

            $this->forgetCachesAfterCommit();

            return $this->freshLand($land);
        });
    }

    public function addValuation(
        Land $land,
        array $data,
        ?int $userId=null
    ): LandValuation{
        return DB::transaction(function()use(
            $land,
            $data,
            $userId
        ){
            $land=Land::query()
                ->whereKey($land->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(
                in_array(
                    $land->status,
                    ['sold','cancelled'],
                    true
                )
            ){
                throw ValidationException::withMessages([
                    'land'=>[
                        'Sold or cancelled land cannot be revalued.'
                    ],
                ]);
            }

            $value=round(
                (float)$data['current_value'],
                2
            );

            if($value<0){
                throw ValidationException::withMessages([
                    'current_value'=>[
                        'Current value cannot be negative.'
                    ],
                ]);
            }

            $previousValue=round(
                (float)($land->current_value??0),
                2
            );

            $valuation=LandValuation::create([
                'land_id'=>$land->id,
                'valuation_date'=>$data['valuation_date'],
                'previous_value'=>$previousValue,
                'current_value'=>$value,
                'valued_by'=>$this->clean(
                    $data['valued_by']??null
                ),
                'notes'=>$this->clean(
                    $data['notes']??null
                ),
                'created_by'=>$userId,
            ]);

            $land->update([
                'current_value'=>$value,
            ]);

            $this->forgetCachesAfterCommit();

            return $valuation->fresh([
                'creator:id,name,email',
            ]);
        });
    }

    public function sell(
        Land $land,
        array $data,
        ?int $userId=null
    ): LandDisposal{
        return DB::transaction(function()use(
            $land,
            $data,
            $userId
        ){
            $land=Land::query()
                ->whereKey($land->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($land->status!=='purchased'){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Only purchased land can be sold.'
                    ],
                ]);
            }

            if(!$land->finance_transaction_id){
                throw ValidationException::withMessages([
                    'land'=>[
                        'Land purchase accounting must be posted before sale.'
                    ],
                ]);
            }

            if($land->disposal()->exists()){
                throw ValidationException::withMessages([
                    'land'=>[
                        'This land already has a disposal record.'
                    ],
                ]);
            }

            $saleDate=$data['sale_date'];

            if(
                $land->purchase_date&&Carbon::parse($saleDate)->lt($land->purchase_date)
            ){
                throw ValidationException::withMessages([
                    'sale_date'=>[
                        'Sale date cannot be earlier than purchase date.'
                    ],
                ]);
            }

            $receiveAccount=$this->cashBankAccount(
                (int)$data['receive_account_id'],
                'receive_account_id'
            );

            $salePrice=round(
                (float)$data['sale_price'],
                2
            );

            $sellingExpense=round(
                (float)($data['selling_expense']??0),
                2
            );

            if($salePrice<=0){
                throw ValidationException::withMessages([
                    'sale_price'=>[
                        'Sale price must be greater than zero.'
                    ],
                ]);
            }

            if($sellingExpense<0){
                throw ValidationException::withMessages([
                    'selling_expense'=>[
                        'Selling expense cannot be negative.'
                    ],
                ]);
            }

            if($sellingExpense>$salePrice){
                throw ValidationException::withMessages([
                    'selling_expense'=>[
                        'Selling expense cannot exceed sale price.'
                    ],
                ]);
            }

            $netSaleAmount=round(
                $salePrice-$sellingExpense,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Land book value
            |--------------------------------------------------------------------------
            |
            | Land is not depreciated here.
            | Purchase price remains its historical accounting book value.
            |
            */
            $bookValue=round(
                (float)$land->purchase_price,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Management net gain/loss
            |--------------------------------------------------------------------------
            |
            | Net sale proceeds less original land book value.
            |
            */
            $gainLoss=round(
                $netSaleAmount-$bookValue,
                2
            );

            $disposal=LandDisposal::create([
                'land_id'=>$land->id,
                'sale_date'=>$saleDate,
                'sale_price'=>$salePrice,
                'selling_expense'=>$sellingExpense,
                'net_sale_amount'=>$netSaleAmount,
                'book_value'=>$bookValue,
                'gain_loss'=>$gainLoss,
                'receive_account_id'=>$receiveAccount->id,
                'buyer_name'=>$this->clean(
                    $data['buyer_name']??null
                ),
                'buyer_phone'=>$this->clean(
                    $data['buyer_phone']??null
                ),
                'reference_no'=>$this->clean(
                    $data['reference_no']??null
                ),
                'notes'=>$this->clean(
                    $data['notes']??null
                ),
                'created_by'=>$userId,
            ]);

            $transaction=$this->postSale(
                $land,
                $disposal,
                $receiveAccount,
                $userId
            );

            $disposal->update([
                'finance_transaction_id'=>$transaction->id,
            ]);

            $land->update([
                'status'=>'sold',
            ]);

            $this->forgetCachesAfterCommit();

            return $disposal->fresh([
                'land',
                'receiveAccount:id,code,name,type,sub_type',
                'financeTransaction.entries.account',
                'creator:id,name,email',
            ]);
        });
    }

    public function delete(Land $land): void
    {
        DB::transaction(function()use($land){
            $land=Land::query()
                ->whereKey($land->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($land->status==='sold'){
                throw ValidationException::withMessages([
                    'land'=>[
                        'Sold land cannot be deleted.'
                    ],
                ]);
            }

            if($land->finance_transaction_id){
                throw ValidationException::withMessages([
                    'land'=>[
                        'Accounting-posted land cannot be deleted.'
                    ],
                ]);
            }

            if($land->disposal()->exists()){
                throw ValidationException::withMessages([
                    'land'=>[
                        'Land with a disposal record cannot be deleted.'
                    ],
                ]);
            }

            $land->delete();

            $this->forgetCachesAfterCommit();
        });
    }

    public function statistics(): array
    {
        return Cache::remember(
            'lands:statistics',
            now()->addMinutes(5),
            function(){
                $landSummary=Land::query()
                    ->selectRaw("
                        COUNT(*) AS total,

                        COALESCE(
                            SUM(
                                CASE
                                    WHEN status='purchased'
                                    THEN purchase_price
                                    ELSE 0
                                END
                            ),
                            0
                        ) AS purchase_value,

                        COALESCE(
                            SUM(
                                CASE
                                    WHEN status='purchased'
                                    THEN current_value
                                    ELSE 0
                                END
                            ),
                            0
                        ) AS current_value,

                        SUM(
                            CASE
                                WHEN status='planned'
                                THEN 1
                                ELSE 0
                            END
                        ) AS planned,

                        SUM(
                            CASE
                                WHEN status='negotiating'
                                THEN 1
                                ELSE 0
                            END
                        ) AS negotiating,

                        SUM(
                            CASE
                                WHEN status='purchased'
                                THEN 1
                                ELSE 0
                            END
                        ) AS purchased,

                        SUM(
                            CASE
                                WHEN status='sold'
                                THEN 1
                                ELSE 0
                            END
                        ) AS sold,

                        SUM(
                            CASE
                                WHEN status='cancelled'
                                THEN 1
                                ELSE 0
                            END
                        ) AS cancelled
                    ")
                    ->first();

                $saleSummary=LandDisposal::query()
                    ->selectRaw("
                        COALESCE(
                            SUM(sale_price),
                            0
                        ) AS total_sales,

                        COALESCE(
                            SUM(selling_expense),
                            0
                        ) AS selling_expense,

                        COALESCE(
                            SUM(net_sale_amount),
                            0
                        ) AS net_sales,

                        COALESCE(
                            SUM(gain_loss),
                            0
                        ) AS profit_loss
                    ")
                    ->first();

                return[
                    'total'=>(int)(
                        $landSummary?->total??0
                    ),

                    'purchase_value'=>round(
                        (float)(
                            $landSummary?->purchase_value??0
                        ),
                        2
                    ),

                    'current_value'=>round(
                        (float)(
                            $landSummary?->current_value??0
                        ),
                        2
                    ),

                    'total_sales'=>round(
                        (float)(
                            $saleSummary?->total_sales??0
                        ),
                        2
                    ),

                    'selling_expense'=>round(
                        (float)(
                            $saleSummary?->selling_expense??0
                        ),
                        2
                    ),

                    'net_sales'=>round(
                        (float)(
                            $saleSummary?->net_sales??0
                        ),
                        2
                    ),

                    'profit_loss'=>round(
                        (float)(
                            $saleSummary?->profit_loss??0
                        ),
                        2
                    ),

                    'planned'=>(int)(
                        $landSummary?->planned??0
                    ),

                    'negotiating'=>(int)(
                        $landSummary?->negotiating??0
                    ),

                    'purchased'=>(int)(
                        $landSummary?->purchased??0
                    ),

                    'sold'=>(int)(
                        $landSummary?->sold??0
                    ),

                    'cancelled'=>(int)(
                        $landSummary?->cancelled??0
                    ),
                ];
            }
        );
    }

    protected function postPurchase(
        Land $land,
        Account $paymentAccount,
        ?int $userId
    ){
        $landAsset=$this->accounting
            ->account('land_asset');

        return $this->accounting->post([
            'idempotency_key'=>
                "land:purchase:{$land->id}",

            'transaction_date'=>
                $land->purchase_date
                    ->toDateString(),

            'type'=>'land_purchase',

            'source_module'=>'land',

            'source_id'=>$land->id,

            'reference_type'=>Land::class,

            'reference_id'=>$land->id,

            'description'=>
                "Land purchase - {$land->land_code} {$land->title}",

            'user_id'=>$userId,

            'entries'=>[
                [
                    'account_id'=>$landAsset->id,
                    'debit'=>round(
                        (float)$land->purchase_price,
                        2
                    ),
                    'credit'=>0,
                    'description'=>
                        "Land asset - {$land->land_code}",
                ],
                [
                    'account_id'=>$paymentAccount->id,
                    'debit'=>0,
                    'credit'=>round(
                        (float)$land->purchase_price,
                        2
                    ),
                    'description'=>
                        "Land purchase payment - {$land->land_code}",
                ],
            ],
        ]);
    }

    protected function postSale(
        Land $land,
        LandDisposal $disposal,
        Account $receiveAccount,
        ?int $userId
    ){
        $landAsset=$this->accounting
            ->account('land_asset');

        $salePrice=round(
            (float)$disposal->sale_price,
            2
        );

        $sellingExpense=round(
            (float)$disposal->selling_expense,
            2
        );

        $bookValue=round(
            (float)$disposal->book_value,
            2
        );

        $cashReceived=round(
            $salePrice-$sellingExpense,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Gross disposal gain/loss
        |--------------------------------------------------------------------------
        |
        | Selling expense is posted separately as an expense.
        |
        */
        $grossGainLoss=round(
            $salePrice-$bookValue,
            2
        );

        $entries=[
            [
                'account_id'=>$receiveAccount->id,
                'debit'=>$cashReceived,
                'credit'=>0,
                'description'=>
                    "Land sale net receipt - {$land->land_code}",
            ],
            [
                'account_id'=>$landAsset->id,
                'debit'=>0,
                'credit'=>$bookValue,
                'description'=>
                    "Remove land asset - {$land->land_code}",
            ],
        ];

        if($sellingExpense>0){
            $sellingExpenseAccount=$this->accounting
                ->account('land_selling_expense');

            $entries[]=[
                'account_id'=>$sellingExpenseAccount->id,
                'debit'=>$sellingExpense,
                'credit'=>0,
                'description'=>
                    "Land selling expense - {$land->land_code}",
            ];
        }

        if($grossGainLoss>0){
            $gainAccount=$this->accounting
                ->account('gain_on_land_sale');

            $entries[]=[
                'account_id'=>$gainAccount->id,
                'debit'=>0,
                'credit'=>$grossGainLoss,
                'description'=>
                    "Gain on land sale - {$land->land_code}",
            ];
        }

        if($grossGainLoss<0){
            $lossAccount=$this->accounting
                ->account('loss_on_land_sale');

            $entries[]=[
                'account_id'=>$lossAccount->id,
                'debit'=>abs($grossGainLoss),
                'credit'=>0,
                'description'=>
                    "Loss on land sale - {$land->land_code}",
            ];
        }

        return $this->accounting->post([
            'idempotency_key'=>
                "land:sale:{$disposal->id}",

            'transaction_date'=>
                $disposal->sale_date
                    ->toDateString(),

            'type'=>'land_sale',

            'source_module'=>'land',

            'source_id'=>$land->id,

            'reference_type'=>LandDisposal::class,

            'reference_id'=>$disposal->id,

            'description'=>
                "Land sale - {$land->land_code} {$land->title}",

            'user_id'=>$userId,

            'entries'=>$entries,
        ]);
    }

    protected function cashBankAccount(
        int $accountId,
        string $field
    ): Account{
        $account=Account::query()
            ->active()
            ->posting()
            ->whereKey($accountId)
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                $field=>[
                    'Selected account is invalid or inactive.'
                ],
            ]);
        }

        if(
            $account->type!=='asset'||
            !in_array(
                $account->sub_type,
                [
                    'cash',
                    'bank',
                    'cash_bank',
                ],
                true
            )
        ){
            throw ValidationException::withMessages([
                $field=>[
                    'Select a valid cash or bank account.'
                ],
            ]);
        }

        return $account;
    }

    protected function generateLandCode(): string
    {
        $last=Land::query()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $next=$last
            ?$last->id+1
            :1;

        $prefix=strtoupper(
            trim(
                (string)setting(
                    'land_code_prefix',
                    'LAND'
                )
            )
        );

        if($prefix===''){
            $prefix='LAND';
        }

        return $prefix.'-'.str_pad(
            (string)$next,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function freshLand(
        Land $land
    ): Land{
        return $land->fresh([
            'paymentAccount:id,code,name,type,sub_type',
            'creator:id,name,email',
            'financeTransaction.entries.account',

            'valuations'=>fn($q)=>
                $q->latest('valuation_date')
                    ->latest('id'),

            'valuations.creator:id,name,email',

            'documents'=>fn($q)=>
                $q->latest('id'),

            'documents.uploader:id,name,email',

            'disposal.receiveAccount:id,code,name,type,sub_type',

            'disposal.creator:id,name,email',

            'disposal.financeTransaction.entries.account',
        ]);
    }

    protected function clean(
        mixed $value
    ): ?string{
        if($value===null){
            return null;
        }

        $value=trim(
            (string)$value
        );

        return $value!==''?$value:null;
    }

    protected function valueChanged(
        mixed $old,
        mixed $new
    ): bool{
        if(
            $old===null&&
            ($new===null||$new==='')
        ){
            return false;
        }

        if(
            $old instanceof \DateTimeInterface
        ){
            $old=$old->format('Y-m-d');
        }

        if(
            is_string($new)
        ){
            $new=trim($new);
        }

        return (string)$old!==(string)$new;
    }

    protected function forgetCachesAfterCommit(): void
    {
        DB::afterCommit(function(){
            $this->forgetCaches();
        });
    }

    public function forgetCaches(): void
    {
        Cache::forget('lands:statistics');
        Cache::forget('dashboard.lands.summary');

        $this->dashboardService
            ->forgetDashboardCaches();
    }
}