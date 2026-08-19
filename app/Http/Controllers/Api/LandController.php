<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Land;
use App\Models\Member;
use App\Services\LandService;
use Illuminate\Http\Request;

class LandController extends Controller
{
    public function __construct(
        protected LandService $landService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:planned,negotiating,purchased,sold,cancelled',
            'district'=>'nullable|string|max:100',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=Land::query()
            ->withCount('investments')
            ->withSum([
                'investments as invested_amount'=>fn($q)=>
                    $q->whereNotIn('status',['cancelled'])
            ],'amount')
            ->latest('id');

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('land_code','like',"%{$search}%")
                    ->orWhere('title','like',"%{$search}%")
                    ->orWhere('district','like',"%{$search}%")
                    ->orWhere('upazila','like',"%{$search}%")
                    ->orWhere('mouza','like',"%{$search}%")
                    ->orWhere('khatian_no','like',"%{$search}%")
                    ->orWhere('dag_no','like',"%{$search}%");
            });
        }

        if(!empty($validated['status'])){
            $query->where(
                'status',
                $validated['status']
            );
        }

        if(!empty($validated['district'])){
            $query->where(
                'district',
                $validated['district']
            );
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                min((int)($validated['per_page']??15),100)
            )
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->landService->statistics()
        ]);
    }

    public function members(Request $request)
    {
        $search=trim(
            (string)$request->input('search','')
        );

        $members=Member::query()
            ->with('user:id,name,email,mobile')
            ->where('status','active')
            ->when($search,function($query)use($search){
                $query->where(function($q)use($search){
                    $q->where('member_code','like',"%{$search}%")
                        ->orWhereHas('user',function($uq)use($search){
                            $uq->where('name','like',"%{$search}%")
                                ->orWhere('email','like',"%{$search}%")
                                ->orWhere('mobile','like',"%{$search}%");
                        });
                });
            })
            ->orderBy('member_code')
            ->limit(30)
            ->get();

        return response()->json([
            'success'=>true,
            'data'=>$members
        ]);
    }

    public function store(Request $request)
    {
        $validated=$this->validateLand($request);

        $land=$this->landService->create(
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Land created successfully.',
            'data'=>$land
        ],201);
    }

    public function show(Land $land)
    {
        return response()->json([
            'success'=>true,
            'data'=>$land->load([
                'investments.member.user',
                'documents'
            ])
        ]);
    }

    public function update(
        Request $request,
        Land $land
    ){
        $validated=$this->validateLand(
            $request,
            true
        );

        $land=$this->landService->update(
            $land,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Land updated successfully.',
            'data'=>$land
        ]);
    }

    public function sell(
        Request $request,
        Land $land
    ){
        $validated=$request->validate([
            'sale_price'=>'required|numeric|min:0.01',
            'selling_expense'=>'nullable|numeric|min:0',
            'sale_date'=>'required|date',
            'buyer_name'=>'nullable|string|max:255',
            'buyer_phone'=>'nullable|string|max:30'
        ]);

        $land=$this->landService->sell(
            $land,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Land sold successfully.',
            'data'=>$land
        ]);
    }

    public function storeInvestment(
        Request $request,
        Land $land
    ){
        $validated=$request->validate([
            'member_id'=>'required|exists:members,id',
            'amount'=>'required|numeric|min:0.01',
            'ownership_percentage'=>'nullable|numeric|min:0.0001|max:100',
            'investment_date'=>'nullable|date',
            'status'=>'nullable|in:pending,active,returned,cancelled',
            'notes'=>'nullable|string|max:3000'
        ]);

        $investment=$this->landService
            ->addInvestment(
                $land,
                $validated
            );

        return response()->json([
            'success'=>true,
            'message'=>'Land investment added successfully.',
            'data'=>$investment->load('member.user')
        ],201);
    }

    public function destroy(Land $land)
    {
        $this->landService->delete($land);

        return response()->json([
            'success'=>true,
            'message'=>'Land deleted successfully.'
        ]);
    }

    protected function validateLand(
        Request $request,
        bool $update=false
    ): array{
        $required=$update?'sometimes|required':'required';

        return $request->validate([
            'title'=>"{$required}|string|max:255",
            'description'=>'nullable|string|max:5000',
            'district'=>'nullable|string|max:100',
            'upazila'=>'nullable|string|max:100',
            'mouza'=>'nullable|string|max:100',
            'khatian_no'=>'nullable|string|max:100',
            'dag_no'=>'nullable|string|max:100',
            'land_area'=>'nullable|numeric|min:0',
            'area_unit'=>'nullable|string|max:30',
            'purchase_price'=>'nullable|numeric|min:0',
            'current_value'=>'nullable|numeric|min:0',
            'purchase_date'=>'nullable|date',
            'seller_name'=>'nullable|string|max:255',
            'seller_phone'=>'nullable|string|max:30',
            'status'=>'nullable|in:planned,negotiating,purchased,sold,cancelled',
            'notes'=>'nullable|string|max:5000'
        ]);
    }
}