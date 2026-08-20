<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Investment;
use App\Models\InvestmentReturn;
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
                'member.user:id,name,email,mobile',
                'paymentAccount:id,code,name'
            ])
            ->withSum([
                'returns as paid_return_total'=>fn($q)=>
                    $q->where('status','paid')
                        ->where('return_type','income')
            ],'amount')
            ->withSum([
                'returns as principal_return_total'=>fn($q)=>
                    $q->where('status','paid')
                        ->where('return_type','principal')
            ],'amount')
            ->latest('investment_date')
            ->latest('id');

        if(!empty($validated['search'])){
            $search=trim($validated['search']);

            $query->where(function($q)use($search){
                $q->where('investment_no','like',"%{$search}%")
                    ->orWhere('title','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhereHas('member',function($mq)use($search){
                        $mq->where('member_code','like',"%{$search}%")
                            ->orWhereHas('user',function($uq)use($search){
                                $uq->where('name','like',"%{$search}%")
                                    ->orWhere('email','like',"%{$search}%")
                                    ->orWhere('mobile','like',"%{$search}%");
                            });
                    });
            });
        }

        if(!empty($validated['status'])){
            $query->where(
                'status',
                $validated['status']
            );
        }

        if(!empty($validated['member_id'])){
            $query->where(
                'member_id',
                $validated['member_id']
            );
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

    public function options()
    {
        $accounts=Account::query()
            ->active()
            ->whereIn('sub_type',['cash','bank'])
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
                'sub_type'
            ]);

        return response()->json([
            'success'=>true,
            'data'=>[
                'cash_bank_accounts'=>$accounts
            ]
        ]);
    }

    public function members(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150'
        ]);

        $search=trim(
            (string)($validated['search']??'')
        );

        $members=Member::query()
            ->with('user:id,name,email,mobile')
            ->where('status','active')
            ->when($search,function($query)use($search){
                $query->where(function($q)use($search){
                    $q->where(
                        'member_code',
                        'like',
                        "%{$search}%"
                    )->orWhereHas(
                        'user',
                        function($uq)use($search){
                            $uq->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            )->orWhere(
                                'mobile',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
                });
            })
            ->orderBy('member_code')
            ->limit(50)
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
            'payment_account_id'=>'required|exists:accounts,id',
            'title'=>'required|string|max:255',
            'description'=>'nullable|string|max:5000',
            'amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'expected_return'=>'nullable|numeric|min:0|max:9999999999999.99',
            'investment_date'=>'required|date',
            'maturity_date'=>'nullable|date|after_or_equal:investment_date',
            'status'=>'nullable|in:pending,active,completed'
        ]);

        $investment=$this->investmentService->create(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Investment created and accounting entry posted successfully.',
            'data'=>$investment
        ],201);
    }

    public function show(Investment $investment)
    {
        $investment->load([
            'member.user',
            'paymentAccount',
            'financeTransaction.entries.account',
            'returns'=>fn($q)=>
                $q->with([
                    'receiveAccount',
                    'financeTransaction.entries.account'
                ])
                ->latest('return_date')
                ->latest('id')
        ]);

        $investment->loadSum([
            'returns as paid_return_total'=>fn($q)=>
                $q->where('status','paid')
                    ->where('return_type','income')
        ],'amount');

        $investment->loadSum([
            'returns as principal_return_total'=>fn($q)=>
                $q->where('status','paid')
                    ->where('return_type','principal')
        ],'amount');

        return response()->json([
            'success'=>true,
            'data'=>$investment
        ]);
    }

    public function update(
        Request $request,
        Investment $investment
    ){
        $validated=$request->validate([
            'member_id'=>'sometimes|required|exists:members,id',
            'payment_account_id'=>'sometimes|required|exists:accounts,id',
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
            'data'=>$investment
        ]);
    }

    public function cancel(
        Request $request,
        Investment $investment
    ){
        $investment=$this->investmentService->cancel(
            $investment,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Investment cancelled and accounting entry reversed successfully.',
            'data'=>$investment
        ]);
    }

    public function destroy(Investment $investment)
    {
        $this->investmentService->delete(
            $investment
        );

        return response()->json([
            'success'=>true,
            'message'=>'Investment deleted successfully.'
        ]);
    }

    public function storeReturn(
        Request $request,
        Investment $investment
    ){
        $validated=$request->validate([
            'return_type'=>'required|in:income,principal',
            'receive_account_id'=>'nullable|exists:accounts,id',
            'amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'return_date'=>'required|date',
            'description'=>'nullable|string|max:3000',
            'status'=>'required|in:pending,paid,cancelled'
        ]);

        $return=$this->investmentService->addReturn(
            $investment,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>$return->status==='paid'
                ?'Investment return added and accounting entry posted successfully.'
                :'Investment return added successfully.',
            'data'=>$return
        ],201);
    }

    public function updateReturn(
        Request $request,
        InvestmentReturn $investmentReturn
    ){
        $validated=$request->validate([
            'return_type'=>'sometimes|required|in:income,principal',
            'receive_account_id'=>'nullable|exists:accounts,id',
            'amount'=>'sometimes|required|numeric|min:0.01|max:9999999999999.99',
            'return_date'=>'sometimes|required|date',
            'description'=>'nullable|string|max:3000',
            'status'=>'sometimes|required|in:pending,paid,cancelled'
        ]);

        $return=$this->investmentService->updateReturn(
            $investmentReturn,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Investment return updated successfully.',
            'data'=>$return
        ]);
    }

    public function destroyReturn(
        InvestmentReturn $investmentReturn
    ){
        $this->investmentService->deleteReturn(
            $investmentReturn
        );

        return response()->json([
            'success'=>true,
            'message'=>'Investment return deleted successfully.'
        ]);
    }
}