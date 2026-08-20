<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Member;
use App\Models\MemberCharge;
use App\Services\ChargeService;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function __construct(
        protected ChargeService $chargeService
    ){}

    public function index(Request $request)
    {
        $query=MemberCharge::query()
            ->with([
                'member.user:id,name,email',
                'incomeAccount:id,code,name',
                'creator:id,name',
            ])
            ->latest('charge_date')
            ->latest('id');

        if($request->filled('search')){
            $search=trim($request->search);

            $query->where(function($q)use($search){
                $q->where('charge_no','like',"%{$search}%")
                    ->orWhere('charge_type','like',"%{$search}%")
                    ->orWhere('reference','like',"%{$search}%")
                    ->orWhereHas('member',function($member)use($search){
                        $member->where('member_code','like',"%{$search}%")
                            ->orWhereHas('user',fn($user)=>$user->where('name','like',"%{$search}%"));
                    });
            });
        }

        if($request->filled('status')){
            $query->where('status',$request->status);
        }

        if($request->filled('from')){
            $query->whereDate('charge_date','>=',$request->from);
        }

        if($request->filled('to')){
            $query->whereDate('charge_date','<=',$request->to);
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(20),
        ]);
    }

    public function summary()
    {
        $active=MemberCharge::query()
            ->whereNotIn('status',['cancelled','waived']);

        return response()->json([
            'success'=>true,
            'data'=>[
                'total_charged'=>(float)(clone $active)->sum('amount'),
                'total_paid'=>(float)(clone $active)->sum('paid_amount'),
                'outstanding'=>(float)(clone $active)->selectRaw('COALESCE(SUM(amount-paid_amount),0) total')->value('total'),
                'unpaid_count'=>(clone $active)->whereIn('status',['unpaid','partial'])->count(),
            ],
        ]);
    }

    public function options()
    {
        return response()->json([
            'success'=>true,
            'data'=>[
                'members'=>Member::query()
                    ->where('status','active')
                    ->with('user:id,name')
                    ->orderBy('member_code')
                    ->get(['id','user_id','member_code']),

                'income_accounts'=>Account::active()
                    ->where('type','income')
                    ->orderBy('code')
                    ->get(['id','code','name']),

                'receive_accounts'=>Account::active()
                    ->whereIn('sub_type',['cash','bank'])
                    ->orderBy('code')
                    ->get(['id','code','name','sub_type']),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>'required|exists:members,id',
            'income_account_id'=>'required|exists:accounts,id',
            'amount'=>'required|numeric|min:0.01|max:999999999999.99',
            'charge_date'=>'required|date',
            'due_date'=>'nullable|date|after_or_equal:charge_date',
            'charge_type'=>'required|string|max:50',
            'reference'=>'nullable|string|max:150',
            'description'=>'nullable|string|max:2000',
        ]);

        $charge=$this->chargeService->create(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Member charge created and posted successfully.',
            'data'=>$charge,
        ],201);
    }

    public function show(MemberCharge $memberCharge)
    {
        return response()->json([
            'success'=>true,
            'data'=>$memberCharge->load([
                'member.user',
                'incomeAccount',
                'payments.receiveAccount',
                'payments.creator',
                'financeTransaction.entries.account',
                'creator',
            ]),
        ]);
    }

    public function pay(Request $request,MemberCharge $memberCharge)
    {
        $validated=$request->validate([
            'amount'=>'required|numeric|min:0.01',
            'receive_account_id'=>'required|exists:accounts,id',
            'payment_date'=>'required|date',
            'payment_method'=>'nullable|string|max:30',
            'reference'=>'nullable|string|max:150',
            'description'=>'nullable|string|max:1000',
        ]);

        $payment=$this->chargeService->pay(
            $memberCharge,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Charge payment posted successfully.',
            'data'=>$payment,
        ],201);
    }

    public function cancel(Request $request,MemberCharge $memberCharge)
    {
        $validated=$request->validate([
            'reason'=>'required|string|max:1000',
        ]);

        $charge=$this->chargeService->cancel(
            $memberCharge,
            $validated['reason'],
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Charge cancelled and reversed successfully.',
            'data'=>$charge,
        ]);
    }

    public function waive(Request $request,MemberCharge $memberCharge)
    {
        $validated=$request->validate([
            'reason'=>'required|string|max:1000',
        ]);

        $charge=$this->chargeService->waive(
            $memberCharge,
            $validated['reason'],
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Charge waived successfully.',
            'data'=>$charge,
        ]);
    }
}