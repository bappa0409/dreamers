<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Member;
use App\Models\MemberCharge;
use App\Services\ApprovalService;
use App\Services\ChargeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberChargeController extends Controller
{
    public function __construct(
        protected ChargeService $chargeService,
        protected ApprovalService $approvalService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:unpaid,partial,paid,waived,cancelled',
            'charge_type'=>'nullable|string|max:50',
            'member_id'=>'nullable|integer|exists:members,id',
            'from'=>'nullable|date_format:Y-m-d',
            'to'=>'nullable|date_format:Y-m-d|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=MemberCharge::query()
            ->with([
                'member:id,user_id,member_code,status',
                'member.user:id,name,email,mobile',
                'incomeAccount:id,code,name,type,sub_type',
                'creator:id,name',
            ])
            ->when(
                !empty($validated['search']),
                function($query)use($validated){
                    $search=trim($validated['search']);

                    $query->where(function($q)use($search){
                        $q->where('charge_no','like',"%{$search}%")
                            ->orWhere('charge_type','like',"%{$search}%")
                            ->orWhere('reference','like',"%{$search}%")
                            ->orWhere('description','like',"%{$search}%")
                            ->orWhereHas('member',function($member)use($search){
                                $member->where(
                                    'member_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'user',
                                    function($user)use($search){
                                        $user->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'mobile',
                                            'like',
                                            "%{$search}%"
                                        );
                                    }
                                );
                            });
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
                !empty($validated['charge_type']),
                fn($q)=>$q->where(
                    'charge_type',
                    $validated['charge_type']
                )
            )
            ->when(
                !empty($validated['member_id']),
                fn($q)=>$q->where(
                    'member_id',
                    $validated['member_id']
                )
            )
            ->when(
                !empty($validated['from']),
                fn($q)=>$q->whereDate(
                    'charge_date',
                    '>=',
                    $validated['from']
                )
            )
            ->when(
                !empty($validated['to']),
                fn($q)=>$q->whereDate(
                    'charge_date',
                    '<=',
                    $validated['to']
                )
            )
            ->latest('charge_date')
            ->latest('id');

        $perPage=min(
            (int)($validated['per_page']??20),
            100
        );

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate($perPage),
        ]);
    }

    public function summary()
{
    $active=MemberCharge::query()
        ->whereNotIn(
            'status',
            ['cancelled','waived']
        );

    return response()->json([
        'success'=>true,
        'data'=>[
            'total_charged'=>round(
                (float)(clone $active)
                    ->sum('amount'),
                2
            ),

            'total_paid'=>round(
                (float)(clone $active)
                    ->sum('paid_amount'),
                2
            ),

            'outstanding'=>round(
                (float)(clone $active)
                    ->selectRaw(
                        'COALESCE(SUM(amount-paid_amount),0) total'
                    )
                    ->value('total'),
                2
            ),

            'unpaid_count'=>(clone $active)
                ->whereIn(
                    'status',
                    ['unpaid','partial']
                )
                ->count(),
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
                    ->with('user:id,name,email,mobile')
                    ->orderBy('member_code')
                    ->get([
                        'id',
                        'user_id',
                        'member_code',
                    ]),

                'income_accounts'=>Account::query()
                    ->where('type','income')
                    ->where('is_active',true)
                    ->whereDoesntHave('children')
                    ->orderBy('code')
                    ->get([
                        'id',
                        'code',
                        'name',
                        'sub_type',
                    ]),

                'receive_accounts'=>Account::query()
                    ->where('type','asset')
                    ->whereIn(
                        'sub_type',
                        ['cash','bank']
                    )
                    ->where('is_active',true)
                    ->whereDoesntHave('children')
                    ->orderBy('code')
                    ->get([
                        'id',
                        'code',
                        'name',
                        'sub_type',
                    ]),

                'charge_types'=>MemberCharge::query()
                    ->whereNotNull('charge_type')
                    ->where('charge_type','!=','')
                    ->distinct()
                    ->orderBy('charge_type')
                    ->pluck('charge_type')
                    ->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>
                'required|integer|exists:members,id',

            'income_account_id'=>
                'required|integer|exists:accounts,id',

            'amount'=>
                'required|numeric|min:0.01|max:9999999999999.99',

            'charge_date'=>
                'required|date_format:Y-m-d',

            'due_date'=>
                'nullable|date_format:Y-m-d|after_or_equal:charge_date',

            'charge_type'=>
                'required|string|max:50',

            'reference'=>
                'nullable|string|max:150',

            'description'=>
                'nullable|string|max:3000',
        ]);

        $charge=DB::transaction(function()use($validated,$request){
            $charge=$this->chargeService->create(
                $validated,
                $request->user()->id
            );

            $this->approvalService->createRequest(
                $charge,
                'Charge',
                'create',
                $request->user()->id,
                'New member charge requires approval before posting.'
            );

            return $charge;
        });

        return response()->json([
            'success'=>true,
            'message'=>'Member charge recorded and sent for approval.',
            'data'=>$charge,
        ],201);
    }

    public function show(MemberCharge $memberCharge)
    {
        return response()->json([
            'success'=>true,
            'data'=>$memberCharge->load([
                'member.user:id,name,email,mobile',
                'incomeAccount',
                'creator:id,name,email',
                'financeTransaction.entries.account',
                'payments'=>fn($q)=>$q
                    ->with([
                        'receiveAccount:id,code,name',
                        'creator:id,name',
                        'financeTransaction:id,transaction_no,status',
                    ])
                    ->latest('payment_date')
                    ->latest('id'),
            ]),
        ]);
    }

    public function update(
        Request $request,
        MemberCharge $memberCharge
    ){
        $validated=$request->validate([
            'member_id'=>
                'sometimes|required|integer|exists:members,id',

            'income_account_id'=>
                'sometimes|required|integer|exists:accounts,id',

            'amount'=>
                'sometimes|required|numeric|min:0.01|max:9999999999999.99',

            'charge_date'=>
                'sometimes|required|date_format:Y-m-d',

            'due_date'=>
                'nullable|date_format:Y-m-d',

            'charge_type'=>
                'sometimes|required|string|max:50',

            'reference'=>
                'nullable|string|max:150',

            'description'=>
                'nullable|string|max:3000',
        ]);

        $charge=$this->chargeService->update(
            $memberCharge,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Member charge updated successfully.',
            'data'=>$charge,
        ]);
    }

    public function pay(
        Request $request,
        MemberCharge $memberCharge
    ){
        $validated=$request->validate([
            'amount'=>
                'required|numeric|min:0.01|max:9999999999999.99',

            'receive_account_id'=>
                'required|integer|exists:accounts,id',

            'payment_date'=>
                'required|date_format:Y-m-d',

            'payment_method'=>
                'nullable|in:cash,bank,mobile_banking,online',

            'reference'=>
                'nullable|string|max:150',

            'description'=>
                'nullable|string|max:2000',
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

    public function cancel(
        Request $request,
        MemberCharge $memberCharge
    ){
        $validated=$request->validate([
            'reason'=>'required|string|max:2000',
        ]);

        $charge=$this->chargeService->cancel(
            $memberCharge,
            $validated['reason'],
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Charge cancelled and journal reversed successfully.',
            'data'=>$charge,
        ]);
    }

    public function waive(
        Request $request,
        MemberCharge $memberCharge
    ){
        $validated=$request->validate([
            'reason'=>'required|string|max:2000',
        ]);

        $charge=$this->chargeService->waive(
            $memberCharge,
            $validated['reason'],
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Charge waived and journal reversed successfully.',
            'data'=>$charge,
        ]);
    }

    public function destroy(
        MemberCharge $memberCharge
    ){
        $this->chargeService->delete(
            $memberCharge
        );

        return response()->json([
            'success'=>true,
            'message'=>'Charge deleted successfully.',
        ]);
    }
}