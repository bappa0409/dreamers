<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Asset;
use App\Services\ApprovalService;
use App\Services\AssetDepreciationService;
use App\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function __construct(
        protected AssetService $assetService,
        protected AssetDepreciationService $assetDepreciationService,
        protected ApprovalService $approvalService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:active,sold,disposed,cancelled',
            'category'=>'nullable|string|max:100',
            'from'=>'nullable|date_format:Y-m-d',
            'to'=>'nullable|date_format:Y-m-d|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=Asset::query()
            ->with([
                'assetAccount:id,code,name',
                'paymentAccount:id,code,name',
                'creator:id,name',
            ])
            ->when(
                !empty($validated['search']),
                function($q)use($validated){
                    $search=trim($validated['search']);

                    $q->where(function($query)use($search){
                        $query
                            ->where('asset_code','like',"%{$search}%")
                            ->orWhere('name','like',"%{$search}%")
                            ->orWhere('category','like',"%{$search}%")
                            ->orWhere('serial_no','like',"%{$search}%")
                            ->orWhere('vendor','like',"%{$search}%")
                            ->orWhere('reference','like',"%{$search}%")
                            ->orWhere('location','like',"%{$search}%");
                    });
                }
            )
            ->when(
                !empty($validated['status']),
                fn($q)=>$q->where(
                    'status',
                    $validated['status']
                )
            )
            ->when(
                !empty($validated['category']),
                fn($q)=>$q->where(
                    'category',
                    $validated['category']
                )
            )
            ->when(
                !empty($validated['from']),
                fn($q)=>$q->whereDate(
                    'purchase_date',
                    '>=',
                    $validated['from']
                )
            )
            ->when(
                !empty($validated['to']),
                fn($q)=>$q->whereDate(
                    'purchase_date',
                    '<=',
                    $validated['to']
                )
            )
            ->latest('purchase_date')
            ->latest('id');

        $perPage=min(
            max(
                (int)($validated['per_page']??20),
                5
            ),
            100
        );

        return response()->json([
            'success'=>true,
            'data'=>$query
                ->paginate($perPage)
                ->withQueryString(),
        ]);
    }

    public function summary()
    {
        $active=Asset::query()
            ->where('status','active');

        return response()->json([
            'success'=>true,
            'data'=>[
                'active_assets'=>(int)(clone $active)->count(),

                'purchase_cost'=>round(
                    (float)(clone $active)
                        ->sum('purchase_cost'),
                    2
                ),

                'accumulated_depreciation'=>round(
                    (float)(clone $active)
                        ->sum('accumulated_depreciation'),
                    2
                ),

                'book_value'=>round(
                    (float)(clone $active)
                        ->selectRaw("
                            COALESCE(
                                SUM(
                                    GREATEST(
                                        purchase_cost-accumulated_depreciation,
                                        0
                                    )
                                ),
                                0
                            ) total
                        ")
                        ->value('total'),
                    2
                ),
            ],
        ]);
    }

    public function options()
    {
        return response()->json([
            'success'=>true,
            'data'=>[
                'asset_accounts'=>Account::query()
                    ->active()
                    ->where('type','asset')
                    ->where('sub_type','fixed_asset')
                    ->whereDoesntHave('children')
                    ->orderBy('code')
                    ->get([
                        'id',
                        'code',
                        'name',
                    ]),

                'cash_bank_accounts'=>Account::query()
                    ->active()
                    ->where('type','asset')
                    ->whereIn(
                        'sub_type',
                        ['cash','bank']
                    )
                    ->whereDoesntHave('children')
                    ->orderBy('code')
                    ->get([
                        'id',
                        'code',
                        'name',
                        'sub_type',
                    ]),

                'categories'=>Asset::query()
                    ->whereNotNull('category')
                    ->where('category','!=','')
                    ->distinct()
                    ->orderBy('category')
                    ->pluck('category')
                    ->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'name'=>'required|string|max:150',
            'category'=>'nullable|string|max:100',

            'asset_account_id'=>
                'required|integer|exists:accounts,id',

            'payment_account_id'=>
                'required|integer|exists:accounts,id',

            'purchase_cost'=>
                'required|numeric|min:0.01|max:9999999999999.99',

            'purchase_date'=>
                'required|date_format:Y-m-d',

            'vendor'=>'nullable|string|max:150',
            'reference'=>'nullable|string|max:150',
            'location'=>'nullable|string|max:150',
            'serial_no'=>'nullable|string|max:150',

            'useful_life_months'=>
                'nullable|integer|min:1|max:1200',

            'salvage_value'=>
                'nullable|numeric|min:0|max:9999999999999.99',

            'description'=>
                'nullable|string|max:3000',
        ]);

        if(
            isset($validated['salvage_value'])&&
            (float)$validated['salvage_value']>
            (float)$validated['purchase_cost']
        ){
            return response()->json([
                'message'=>'Salvage value cannot exceed purchase cost.',
                'errors'=>[
                    'salvage_value'=>[
                        'Salvage value cannot exceed purchase cost.'
                    ],
                ],
            ],422);
        }

        $asset=DB::transaction(function()use($validated,$request){
            $asset=$this->assetService
                ->create(
                    $validated,
                    $request->user()->id
                );

            $this->approvalService->createRequest(
                $asset,
                'Asset',
                'create',
                $request->user()->id,
                'New asset requires approval before posting.'
            );

            return $asset;
        });

        return response()->json([
            'success'=>true,
            'message'=>'Asset recorded and sent for approval.',
            'data'=>$asset,
        ],201);
    }

    public function show(Asset $asset)
    {
        return response()->json([
            'success'=>true,
            'data'=>$asset->load([
                'assetAccount',
                'paymentAccount',
                'financeTransaction.entries.account',
                'disposalTransaction.entries.account',
                'creator',
            ]),
        ]);
    }

    public function update(
        Request $request,
        Asset $asset
    ){
        $validated=$request->validate([
            'name'=>'sometimes|required|string|max:150',
            'category'=>'nullable|string|max:100',

            'asset_account_id'=>
                'sometimes|required|integer|exists:accounts,id',

            'payment_account_id'=>
                'sometimes|required|integer|exists:accounts,id',

            'purchase_cost'=>
                'sometimes|required|numeric|min:0.01|max:9999999999999.99',

            'purchase_date'=>
                'sometimes|required|date_format:Y-m-d',

            'vendor'=>'nullable|string|max:150',
            'reference'=>'nullable|string|max:150',
            'location'=>'nullable|string|max:150',
            'serial_no'=>'nullable|string|max:150',

            'useful_life_months'=>
                'nullable|integer|min:1|max:1200',

            'salvage_value'=>
                'nullable|numeric|min:0|max:9999999999999.99',

            'description'=>
                'nullable|string|max:3000',
        ]);

        $purchaseCost=
            (float)(
                $validated['purchase_cost']
                ??$asset->purchase_cost
            );

        if(
            array_key_exists(
                'salvage_value',
                $validated
            )&&
            $validated['salvage_value']!==null&&
            (float)$validated['salvage_value']>
            $purchaseCost
        ){
            return response()->json([
                'message'=>'Salvage value cannot exceed purchase cost.',
                'errors'=>[
                    'salvage_value'=>[
                        'Salvage value cannot exceed purchase cost.'
                    ],
                ],
            ],422);
        }

        $asset=$this->assetService
            ->update(
                $asset,
                $validated
            );

        return response()->json([
            'success'=>true,
            'message'=>'Asset updated successfully.',
            'data'=>$asset,
        ]);
    }

    public function sell(
        Request $request,
        Asset $asset
    ){
        $validated=$request->validate([
            'amount'=>
                'required|numeric|min:0|max:9999999999999.99',

            'receive_account_id'=>
                'required|integer|exists:accounts,id',

            'disposal_date'=>[
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.
                    $asset->purchase_date->toDateString(),
            ],

            'note'=>'nullable|string|max:2000',
        ]);

        $asset=$this->assetService
            ->sell(
                $asset,
                $validated,
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>'Asset sold and accounting entry posted successfully.',
            'data'=>$asset,
        ]);
    }

    public function dispose(
        Request $request,
        Asset $asset
    ){
        $validated=$request->validate([
            'disposal_date'=>[
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.
                    $asset->purchase_date->toDateString(),
            ],

            'note'=>'required|string|max:2000',
        ]);

        $asset=$this->assetService
            ->dispose(
                $asset,
                $validated,
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>'Asset disposed and accounting entry posted successfully.',
            'data'=>$asset,
        ]);
    }

    public function depreciate(
        Request $request,
        Asset $asset
    ){
        $validated=$request->validate([
            'depreciation_date'=>'required|date_format:Y-m-d',
            'description'=>'nullable|string|max:1000',
        ]);

        $depreciation=$this->assetDepreciationService->post(
            $asset,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Asset depreciation posted successfully.',
            'data'=>$depreciation,
        ],201);
    }

public function depreciations(
    Asset $asset
){
    $depreciations=$asset
        ->depreciations()
        ->with([
            'creator:id,name',
            'financeTransaction.entries.account',
        ])
        ->latest('depreciation_date')
        ->latest('id')
        ->get();

    return response()->json([
        'success'=>true,
        'data'=>[
            'asset'=>[
                'id'=>$asset->id,
                'asset_code'=>$asset->asset_code,
                'name'=>$asset->name,
                'purchase_cost'=>round(
                    (float)$asset->purchase_cost,
                    2
                ),
                'salvage_value'=>round(
                    (float)$asset->salvage_value,
                    2
                ),
                'accumulated_depreciation'=>round(
                    (float)$asset->accumulated_depreciation,
                    2
                ),
                'book_value'=>$asset->book_value,
                'monthly_depreciation'=>$asset->monthly_depreciation,
                'remaining_depreciable_amount'=>
                    $asset->remaining_depreciable_amount,
            ],
            'depreciations'=>$depreciations,
        ],
    ]);
}

    public function cancel(
        Request $request,
        Asset $asset
    ){
        $validated=$request->validate([
            'reason'=>'required|string|max:2000',
        ]);

        $asset=$this->assetService
            ->cancel(
                $asset,
                $validated['reason'],
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>'Asset purchase cancelled and reversed successfully.',
            'data'=>$asset,
        ]);
    }

    public function destroy(
        Asset $asset
    ){
        $this->assetService
            ->delete($asset);

        return response()->json([
            'success'=>true,
            'message'=>'Asset deleted successfully.',
        ]);
    }
}