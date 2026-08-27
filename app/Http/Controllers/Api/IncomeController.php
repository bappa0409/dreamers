<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Income;
use App\Models\Member;
use App\Services\IncomeService;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct(
        protected IncomeService $incomeService
    ){}

    public function index(Request $request)
    {
        $query=Income::query()
            ->with([
                'member.user:id,name,email',
                'incomeAccount:id,code,name',
                'receiveAccount:id,code,name',
                'creator:id,name',
            ])
            ->latest('income_date')
            ->latest('id');

        if($request->filled('search')){
            $search=trim($request->search);

            $query->where(function($q)use($search){
                $q->where('income_no','like',"%{$search}%")
                    ->orWhere('reference','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhereHas('member.user',fn($user)=>$user->where('name','like',"%{$search}%"));
            });
        }

        if($request->filled('status')){
            $query->where('status',$request->status);
        }

        if($request->filled('from')){
            $query->whereDate('income_date','>=',$request->from);
        }

        if($request->filled('to')){
            $query->whereDate('income_date','<=',$request->to);
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(20),
        ]);
    }

    public function options()
    {
        return response()->json([
            'success'=>true,
            'data'=>[
                'income_accounts'=>Account::active()
    ->where('type','income')
    ->whereDoesntHave('children')
    ->orderBy('code')
    ->get(['id','code','name']),

'receive_accounts'=>Account::active()
    ->where('type','asset')
    ->whereIn('sub_type',['cash','bank'])
    ->whereDoesntHave('children')
    ->orderBy('code')
    ->get(['id','code','name','sub_type']),

                'members'=>Member::query()
                    ->where('status','active')
                    ->with('user:id,name')
                    ->orderBy('member_code')
                    ->get(['id','user_id','member_code']),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>'nullable|exists:members,id',
            'income_account_id'=>'required|exists:accounts,id',
            'receive_account_id'=>'required|exists:accounts,id',
            'amount'=>'required|numeric|min:0.01|max:999999999999.99',
            'income_date'=>'required|date',
            'reference'=>'nullable|string|max:150',
            'description'=>'nullable|string|max:2000',
        ]);

        $income=$this->incomeService->create(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Income recorded and posted successfully.',
            'data'=>$income,
        ],201);
    }

    public function show(Income $income)
    {
        return response()->json([
            'success'=>true,
            'data'=>$income->load([
                'member.user',
                'incomeAccount',
                'receiveAccount',
                'financeTransaction.entries.account',
                'creator',
            ]),
        ]);
    }

    public function update(
    Request $request,
    Income $income
){
    $validated=$request->validate([
        'member_id'=>'nullable|exists:members,id',
        'income_account_id'=>'sometimes|exists:accounts,id',
        'receive_account_id'=>'sometimes|exists:accounts,id',
        'amount'=>'sometimes|numeric|min:0.01|max:999999999999.99',
        'income_date'=>'sometimes|date',
        'reference'=>'nullable|string|max:150',
        'description'=>'nullable|string|max:2000',
    ]);

    $income=$this->incomeService->update(
        $income,
        $validated
    );

    return response()->json([
        'success'=>true,
        'message'=>'Income updated successfully.',
        'data'=>$income,
    ]);
}

public function destroy(Income $income)
{
    $this->incomeService->delete($income);

    return response()->json([
        'success'=>true,
        'message'=>'Income deleted successfully.',
    ]);
}

    public function cancel(Request $request,Income $income)
    {
        $validated=$request->validate([
            'reason'=>'required|string|max:1000',
        ]);

        $income=$this->incomeService->cancel(
            $income,
            $validated['reason'],
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Income cancelled and reversed successfully.',
            'data'=>$income,
        ]);
    }
}