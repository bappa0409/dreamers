<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Asset;
use App\Services\AssetService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function __construct(
        protected AssetService $assetService
    ){}

    public function index(Request $request)
    {
        $query=Asset::query()
            ->with([
                'assetAccount:id,code,name',
                'paymentAccount:id,code,name',
                'creator:id,name',
            ])
            ->latest('purchase_date')
            ->latest('id');

        if($request->filled('search')){
            $search=trim($request->search);

            $query->where(function($q)use($search){
                $q->where('asset_code','like',"%{$search}%")
                    ->orWhere('name','like',"%{$search}%")
                    ->orWhere('category','like',"%{$search}%")
                    ->orWhere('serial_no','like',"%{$search}%")
                    ->orWhere('vendor','like',"%{$search}%")
                    ->orWhere('reference','like',"%{$search}%");
            });
        }

        if($request->filled('status')){
            $query->where('status',$request->status);
        }

        if($request->filled('category')){
            $query->where('category',$request->category);
        }

        if($request->filled('from')){
            $query->whereDate('purchase_date','>=',$request->from);
        }

        if($request->filled('to')){
            $query->whereDate('purchase_date','<=',$request->to);
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(20),
        ]);
    }

    public function summary()
    {
        $active=Asset::query()->where('status','active');

        return response()->json([
            'success'=>true,
            'data'=>[
                'active_assets'=>(clone $active)->count(),
                'purchase_cost'=>(float)(clone $active)->sum('purchase_cost'),
                'accumulated_depreciation'=>(float)(clone $active)->sum('accumulated_depreciation'),
                'book_value'=>(float)(clone $active)
                    ->selectRaw('COALESCE(SUM(purchase_cost-accumulated_depreciation),0) total')
                    ->value('total'),
            ],
        ]);
    }

    public function options()
    {
        return response()->json([
            'success'=>true,
            'data'=>[
                'asset_accounts'=>Account::active()
                    ->where('type','asset')
                    ->where('sub_type','fixed_asset')
                    ->orderBy('code')
                    ->get(['id','code','name']),

                'cash_bank_accounts'=>Account::active()
                    ->whereIn('sub_type',['cash','bank'])
                    ->orderBy('code')
                    ->get(['id','code','name','sub_type']),

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
            'asset_account_id'=>'required|exists:accounts,id',
            'payment_account_id'=>'required|exists:accounts,id',
            'purchase_cost'=>'required|numeric|min:0.01|max:999999999999.99',
            'purchase_date'=>'required|date',
            'vendor'=>'nullable|string|max:150',
            'reference'=>'nullable|string|max:150',
            'location'=>'nullable|string|max:150',
            'serial_no'=>'nullable|string|max:150',
            'useful_life_months'=>'nullable|integer|min:1|max:1200',
            'salvage_value'=>'nullable|numeric|min:0',
            'description'=>'nullable|string|max:2000',
        ]);

        $asset=$this->assetService->create(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Asset created and posted successfully.',
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

    public function sell(Request $request,Asset $asset)
    {
        $validated=$request->validate([
            'amount'=>'required|numeric|min:0',
            'receive_account_id'=>'required|exists:accounts,id',
            'disposal_date'=>'required|date',
            'note'=>'nullable|string|max:1000',
        ]);

        $asset=$this->assetService->sell(
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

    public function dispose(Request $request,Asset $asset)
    {
        $validated=$request->validate([
            'disposal_date'=>'required|date',
            'note'=>'required|string|max:1000',
        ]);

        $asset=$this->assetService->dispose(
            $asset,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Asset disposed successfully.',
            'data'=>$asset,
        ]);
    }

    public function cancel(Request $request,Asset $asset)
    {
        $validated=$request->validate([
            'reason'=>'required|string|max:1000',
        ]);

        $asset=$this->assetService->cancel(
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
}