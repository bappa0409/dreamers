<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Loan;
use App\Models\Member;
use App\Services\LoanService;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:pending,approved,rejected,active,overdue,repaid,cancelled,defaulted,written_off',
            'member_id'=>'nullable|integer|exists:members,id',
            'from'=>'nullable|date_format:Y-m-d',
            'to'=>'nullable|date_format:Y-m-d|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=Loan::query()
            ->select([
                'id',
                'loan_no',
                'member_id',
                'requested_amount',
                'approved_amount',
                'interest_rate',
                'interest_amount',
                'total_payable',
                'duration_months',
                'request_date',
                'maturity_date',
                'status',
                'purpose',
                'disbursement_date'
            ])
            ->with([
                'member:id,user_id,member_code',
                'member.user:id,name,email'
            ])
            ->when(
                $validated['status']??null,
                fn($q,$status)=>$q->where('status',$status)
            )
            ->when(
                $validated['member_id']??null,
                fn($q,$id)=>$q->where('member_id',$id)
            )
            ->when(
                $validated['from']??null,
                fn($q,$date)=>$q->whereDate('request_date','>=',$date)
            )
            ->when(
                $validated['to']??null,
                fn($q,$date)=>$q->whereDate('request_date','<=',$date)
            )
            ->when(
                $validated['search']??null,
                function($q,$search){
                    $search=trim($search);

                    $q->where(function($q)use($search){
                        $q->where('loan_no','like',"%{$search}%")
                            ->orWhere('purpose','like',"%{$search}%")
                            ->orWhereHas(
                                'member',
                                fn($m)=>$m
                                    ->where('member_code','like',"%{$search}%")
                                    ->orWhereHas(
                                        'user',
                                        fn($u)=>$u->where('name','like',"%{$search}%")
                                    )
                            );
                    });
                }
            )
            ->latest('id');

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                $validated['per_page']??15
            )->withQueryString()
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->loanService->statistics()
        ]);
    }

    public function options(Request $request)
    {
        $search=trim((string)$request->query('member_search',''));

        return response()->json([
            'success'=>true,
            'data'=>[
                'members'=>Member::query()
                    ->select(['id','user_id','member_code'])
                    ->with('user:id,name')
                    ->where('status','active')
                    ->when($search,function($q)use($search){
                        $q->where(function($q)use($search){
                            $q->where('member_code','like',"%{$search}%")
                                ->orWhereHas(
                                    'user',
                                    fn($u)=>$u->where('name','like',"%{$search}%")
                                );
                        });
                    })
                    ->limit(30)
                    ->get(),

                'cash_bank_accounts'=>Account::query()
                    ->active()
                    ->whereIn('sub_type',['cash','bank'])
                    ->whereDoesntHave('children')
                    ->orderBy('code')
                    ->get(['id','code','name','sub_type']),

                'settings'=>[
                    'interest_rate'=>(float)setting(
                        'loan_default_interest_rate',
                        10
                    ),
                    'duration_months'=>(int)setting(
                        'loan_default_duration_months',
                        12
                    ),
                    'maximum_amount'=>(float)setting(
                        'loan_max_amount',
                        0
                    )
                ]
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>'required|integer|exists:members,id',
            'requested_amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'request_date'=>'nullable|date_format:Y-m-d',
            'purpose'=>'nullable|string|max:5000',
            'notes'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Loan request created successfully.',
            'data'=>$this->loanService->createRequest(
                $validated,
                $request->user()->id
            )
        ],201);
    }

    public function show(Loan $loan)
    {
        $loan->load([
            'member.user',
            'approver:id,name',
            'rejector:id,name',
            'creator:id,name',
            'disbursementAccount',
            'financeTransaction.entries.account',
            'repayments.receiveAccount',
            'repayments.receiver:id,name',
            'repayments.financeTransaction.entries.account'
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$loan
        ]);
    }

    public function update(Request $request,Loan $loan)
    {
        $validated=$request->validate([
            'requested_amount'=>'sometimes|required|numeric|min:0.01|max:9999999999999.99',
            'request_date'=>'sometimes|required|date_format:Y-m-d',
            'purpose'=>'nullable|string|max:5000',
            'notes'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Loan request updated successfully.',
            'data'=>$this->loanService->updateRequest(
                $loan,
                $validated
            )
        ]);
    }

    public function approve(Request $request,Loan $loan)
    {
        $validated=$request->validate([
            'approved_amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'interest_rate'=>'required|numeric|min:0|max:100',
            'duration_months'=>'required|integer|min:1|max:120'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Loan approved successfully.',
            'data'=>$this->loanService->approve(
                $loan,
                $validated,
                $request->user()->id
            )
        ]);
    }

    public function reject(Request $request,Loan $loan)
    {
        $validated=$request->validate([
            'rejection_reason'=>'required|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Loan rejected successfully.',
            'data'=>$this->loanService->reject(
                $loan,
                $validated['rejection_reason'],
                $request->user()->id
            )
        ]);
    }

    public function cancel(Request $request,Loan $loan)
    {
        return response()->json([
            'success'=>true,
            'message'=>'Loan cancelled successfully.',
            'data'=>$this->loanService->cancel(
                $loan,
                $request->user()->id
            )
        ]);
    }

    public function disburse(Request $request,Loan $loan)
    {
        $validated=$request->validate([
            'disbursement_account_id'=>'required|integer|exists:accounts,id',
            'disbursement_date'=>'required|date_format:Y-m-d'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Loan disbursed successfully.',
            'data'=>$this->loanService->disburse(
                $loan,
                $validated,
                $request->user()->id
            )
        ]);
    }

    public function repay(Request $request,Loan $loan)
    {
        $validated=$request->validate([
            'receive_account_id'=>'required|integer|exists:accounts,id',
            'total_amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'repayment_date'=>'required|date_format:Y-m-d',
            'notes'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Loan repayment completed successfully.',
            'data'=>$this->loanService->repay(
                $loan,
                $validated,
                $request->user()->id
            )
        ],201);
    }

    public function markDefaulted(Loan $loan)
    {
        return response()->json([
            'success'=>true,
            'message'=>'Loan marked as defaulted.',
            'data'=>$this->loanService->markDefaulted($loan)
        ]);
    }
}