<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Http\Request;

class MemberLoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService
    ){}

    public function index(Request $request)
    {
        $member=$request->user()->member;

        abort_unless($member,403);

        $validated=$request->validate([
            'status'=>'nullable|in:pending,approved,rejected,active,overdue,repaid,cancelled,defaulted,written_off',
            'per_page'=>'nullable|integer|min:5|max:50'
        ]);

        $query=$member->loans()
            ->select([
                'id',
                'loan_no',
                'requested_amount',
                'approved_amount',
                'interest_rate',
                'interest_amount',
                'total_payable',
                'duration_months',
                'request_date',
                'disbursement_date',
                'maturity_date',
                'status',
                'purpose',
                'rejection_reason'
            ])
            ->withSum('repayments as paid_total','total_amount')
            ->when(
                $validated['status']??null,
                fn($q,$status)=>$q->where('status',$status)
            )
            ->latest('id');

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                $validated['per_page']??15
            )->withQueryString()
        ]);
    }

    public function store(Request $request)
    {
        $member=$request->user()->member;

        abort_unless(
            $member&&$member->status==='active',
            403
        );

        $validated=$request->validate([
            'requested_amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'purpose'=>'required|string|max:5000',
            'notes'=>'nullable|string|max:5000'
        ]);

        $validated['member_id']=$member->id;
        $validated['request_date']=now()->toDateString();

        return response()->json([
            'success'=>true,
            'message'=>'Loan request submitted successfully.',
            'data'=>$this->loanService->createRequest(
                $validated,
                $request->user()->id
            )
        ],201);
    }

    public function show(Request $request,Loan $loan)
    {
        abort_unless(
            $loan->member_id===$request->user()->member?->id,
            403
        );

        $loan->load([
            'disbursementAccount:id,code,name',
            'repayments'=>fn($q)=>$q
                ->with('receiveAccount:id,code,name')
                ->latest('repayment_date')
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$loan
        ]);
    }

    public function cancel(Request $request,Loan $loan)
    {
        abort_unless(
            $loan->member_id===$request->user()->member?->id,
            403
        );

        return response()->json([
            'success'=>true,
            'message'=>'Loan request cancelled.',
            'data'=>$this->loanService->cancel(
                $loan,
                $request->user()->id
            )
        ]);
    }
}