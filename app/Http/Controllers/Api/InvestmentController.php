<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Member;
use App\Services\InvestmentService;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    public function __construct(
        protected InvestmentService $investmentService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:pending,active,completed,cancelled',
            'member_id'=>'nullable|integer|exists:members,id',
            'from'=>'nullable|date',
            'to'=>'nullable|date|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=Investment::query()
            ->with([
                'member.user:id,name,email,mobile'
            ])
            ->withSum([
                'returns as paid_return_total'=>fn($q)=>
                    $q->where('status','paid')
            ],'amount')
            ->latest('id');

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('investment_no','like',"%{$search}%")
                    ->orWhere('title','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhereHas('member.user',function($uq)use($search){
                        $uq->where('name','like',"%{$search}%")
                            ->orWhere('email','like',"%{$search}%")
                            ->orWhere('mobile','like',"%{$search}%");
                    });
            });
        }

        if(!empty($validated['status'])){
            $query->where('status',$validated['status']);
        }

        if(!empty($validated['member_id'])){
            $query->where('member_id',$validated['member_id']);
        }

        if(!empty($validated['from'])){
            $query->whereDate(
                'investment_date',
                '>=',
                $validated['from']
            );
        }

        if(!empty($validated['to'])){
            $query->whereDate(
                'investment_date',
                '<=',
                $validated['to']
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
            'data'=>$this->investmentService->statistics()
        ]);
    }

    public function members(Request $request)
    {
        $search=trim((string)$request->input('search',''));

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
        $validated=$request->validate([
            'member_id'=>'required|exists:members,id',
            'title'=>'required|string|max:255',
            'description'=>'nullable|string|max:5000',
            'amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'expected_return'=>'nullable|numeric|min:0|max:9999999999999.99',
            'investment_date'=>'required|date',
            'maturity_date'=>'nullable|date|after_or_equal:investment_date',
            'status'=>'nullable|in:pending,active,completed,cancelled'
        ]);

        $validated['status']=$validated['status']??'pending';
        $validated['expected_return']=$validated['expected_return']??0;

        $investment=$this->investmentService->create($validated);

        return response()->json([
            'success'=>true,
            'message'=>'Investment created successfully.',
            'data'=>$investment->load('member.user')
        ],201);
    }

    public function show(Investment $investment)
    {
        return response()->json([
            'success'=>true,
            'data'=>$investment->load([
                'member.user',
                'returns'=>fn($q)=>$q->latest('return_date')
            ])->loadSum([
                'returns as paid_return_total'=>fn($q)=>
                    $q->where('status','paid')
            ],'amount')
        ]);
    }

    public function update(Request $request,Investment $investment)
    {
        $validated=$request->validate([
            'member_id'=>'sometimes|required|exists:members,id',
            'title'=>'sometimes|required|string|max:255',
            'description'=>'nullable|string|max:5000',
            'amount'=>'sometimes|required|numeric|min:0.01|max:9999999999999.99',
            'expected_return'=>'nullable|numeric|min:0|max:9999999999999.99',
            'investment_date'=>'sometimes|required|date',
            'maturity_date'=>'nullable|date',
            'status'=>'sometimes|required|in:pending,active,completed,cancelled'
        ]);

        $investment=$this->investmentService->update(
            $investment,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Investment updated successfully.',
            'data'=>$investment->load('member.user')
        ]);
    }

    public function destroy(Investment $investment)
    {
        $this->investmentService->delete($investment);

        return response()->json([
            'success'=>true,
            'message'=>'Investment deleted successfully.'
        ]);
    }

    public function storeReturn(Request $request,Investment $investment)
    {
        $validated=$request->validate([
            'amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'return_date'=>'required|date',
            'description'=>'nullable|string|max:3000',
            'status'=>'required|in:pending,paid,cancelled'
        ]);

        $return=$this->investmentService->addReturn(
            $investment,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Investment return added successfully.',
            'data'=>$return
        ],201);
    }
}