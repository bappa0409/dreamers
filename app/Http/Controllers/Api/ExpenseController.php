<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Expense;
use App\Services\ApprovalService;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService,
        protected ApprovalService $approvalService
    ){}

    public function index(Request $request)
    {
        $query=Expense::query()
            ->with([
                'expenseAccount:id,code,name',
                'paymentAccount:id,code,name',
                'creator:id,name',
            ])
            ->latest('expense_date')
            ->latest('id');

        if($request->filled('search')){
            $search=trim($request->search);

            $query->where(function($q)use($search){
                $q->where('expense_no','like',"%{$search}%")
                    ->orWhere('payee','like',"%{$search}%")
                    ->orWhere('reference','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%");
            });
        }

        if($request->filled('status')){
            $query->where('status',$request->status);
        }

        if($request->filled('from')){
            $query->whereDate('expense_date','>=',$request->from);
        }

        if($request->filled('to')){
            $query->whereDate('expense_date','<=',$request->to);
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
                'expense_accounts'=>Account::active()
    ->where('type','expense')
    ->whereDoesntHave('children')
    ->orderBy('code')
    ->get(['id','code','name']),

'payment_accounts'=>Account::active()
    ->where('type','asset')
    ->whereIn('sub_type',['cash','bank'])
    ->whereDoesntHave('children')
    ->orderBy('code')
    ->get(['id','code','name','sub_type']),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'expense_account_id'=>'required|exists:accounts,id',
            'payment_account_id'=>'required|exists:accounts,id',
            'amount'=>'required|numeric|min:0.01|max:999999999999.99',
            'expense_date'=>'required|date',
            'payee'=>'nullable|string|max:150',
            'reference'=>'nullable|string|max:150',
            'description'=>'nullable|string|max:2000',
        ]);

        $expense=DB::transaction(function()use($validated,$request){
            $expense=$this->expenseService->create(
                $validated,
                $request->user()->id
            );

            $this->approvalService->createRequest(
                $expense,
                'Expense',
                'create',
                $request->user()->id,
                'New expense requires approval before posting.'
            );

            return $expense;
        });

        return response()->json([
            'success'=>true,
            'message'=>'Expense recorded and sent for approval.',
            'data'=>$expense,
        ],201);
    }

    public function show(Expense $expense)
    {
        return response()->json([
            'success'=>true,
            'data'=>$expense->load([
                'expenseAccount',
                'paymentAccount',
                'financeTransaction.entries.account',
                'creator',
            ]),
        ]);
    }

    public function update(
    Request $request,
    Expense $expense
){
    $validated=$request->validate([
        'expense_account_id'=>'sometimes|exists:accounts,id',
        'payment_account_id'=>'sometimes|exists:accounts,id',
        'amount'=>'sometimes|numeric|min:0.01|max:999999999999.99',
        'expense_date'=>'sometimes|date',
        'payee'=>'nullable|string|max:150',
        'reference'=>'nullable|string|max:150',
        'description'=>'nullable|string|max:2000',
    ]);

    $expense=$this->expenseService->update(
        $expense,
        $validated
    );

    return response()->json([
        'success'=>true,
        'message'=>'Expense updated successfully.',
        'data'=>$expense,
    ]);
}

public function destroy(Expense $expense)
{
    $this->expenseService->delete($expense);

    return response()->json([
        'success'=>true,
        'message'=>'Expense deleted successfully.',
    ]);
}
    public function cancel(Request $request,Expense $expense)
    {
        $validated=$request->validate([
            'reason'=>'required|string|max:1000',
        ]);

        $expense=$this->expenseService->cancel(
            $expense,
            $validated['reason'],
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Expense cancelled and reversed successfully.',
            'data'=>$expense,
        ]);
    }
}