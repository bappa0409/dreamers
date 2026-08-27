<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanRepayment;
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
            'search'=>'nullable|string|max:150',
            'per_page'=>'nullable|integer|min:5|max:50'
        ]);

        $summary=$member->loans()
            ->selectRaw("
                COUNT(*) total,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
                SUM(CASE WHEN status IN ('active','overdue','defaulted') THEN 1 ELSE 0 END) active,
                COALESCE(
                    SUM(
                        CASE
                            WHEN status IN ('active','overdue','defaulted')
                            THEN total_payable
                            ELSE 0
                        END
                    ),
                    0
                ) active_total_payable
            ")
            ->first();

        $activeLoanIds=$member->loans()
            ->whereIn(
                'status',
                ['active','overdue','defaulted']
            )
            ->select('id');

        $activeRepaid=(float)LoanRepayment::query()
            ->whereIn('loan_id',$activeLoanIds)
            ->sum('total_amount');

        $outstanding=max(
            round(
                (float)($summary->active_total_payable??0)-
                $activeRepaid,
                2
            ),
            0
        );

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
            ->when(
                !empty($validated['search']),
                function($q)use($validated){
                    $search=trim($validated['search']);

                    $q->where(function($query)use($search){
                        $query->where('loan_no','like',"%{$search}%")
                            ->orWhere('purpose','like',"%{$search}%")
                            ->orWhere('status','like',"%{$search}%");
                    });
                }
            )
            ->latest('id');

        $paginator=$query->paginate(
            $validated['per_page']??15
        )->withQueryString();

        $paginator->getCollection()->transform(
            function(Loan $loan){
                $paid=round(
                    (float)($loan->paid_total??0),
                    2
                );

                $loan->setAttribute(
                    'outstanding_amount',
                    in_array(
                        $loan->status,
                        ['active','overdue','defaulted'],
                        true
                    )
                        ?max(
                            round(
                                (float)$loan->total_payable-$paid,
                                2
                            ),
                            0
                        )
                        :0
                );

                return $loan;
            }
        );

        $payload=$paginator->toArray();
        $payload['summary']=[
            'total'=>(int)($summary->total??0),
            'pending'=>(int)($summary->pending??0),
            'active'=>(int)($summary->active??0),
            'outstanding'=>$outstanding,
        ];

        return response()->json([
            'success'=>true,
            'data'=>$payload
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

        $loan->setAttribute(
            'outstanding_amount',
            in_array(
                $loan->status,
                ['active','overdue','defaulted'],
                true
            )
                ?max(
                    round(
                        (float)$loan->total_payable-
                        (float)$loan->repayments->sum('total_amount'),
                        2
                    ),
                    0
                )
                :0
        );

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