<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Member;
use App\Models\WelfareDocument;
use App\Models\WelfareFund;
use App\Models\WelfareRequest;
use App\Services\WelfareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WelfareController extends Controller
{
    public function __construct(
        protected WelfareService $service
    ){}

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->service->statistics()
        ]);
    }

    public function funds()
    {
        $funds=WelfareFund::query()
            ->with('expenseAccount:id,code,name')
            ->withSum(
                'allocations as allocated_amount',
                'amount'
            )
            ->withSum([
                'requests as committed_amount'=>fn($q)=>
                    $q->whereIn(
                        'status',
                        ['approved','completed']
                    )
            ],'approved_amount')
            ->withSum([
                'requests as spent_amount'=>fn($q)=>
                    $q->where(
                        'status',
                        'completed'
                    )
            ],'approved_amount')
            ->latest('id')
            ->get()
            ->map(function($fund){
                $fund->available_amount=max(
                    0,
                    round(
                        (float)$fund->allocated_amount-
                        (float)$fund->committed_amount,
                        2
                    )
                );

                return $fund;
            });

        return response()->json([
            'success'=>true,
            'data'=>$funds
        ]);
    }

    public function storeFund(Request $request)
    {
        $data=$request->validate([
            'code'=>
                'required|string|max:50|alpha_dash|unique:welfare_funds,code',

            'name'=>'required|string|max:150',
            'description'=>'nullable|string|max:5000',

            'expense_account_id'=>
                'required|integer|exists:accounts,id',

            'start_date'=>'nullable|date_format:Y-m-d',
            'end_date'=>
                'nullable|date_format:Y-m-d|after_or_equal:start_date',

            'is_active'=>'nullable|boolean'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Welfare fund created.',
            'data'=>$this->service->createFund(
                $data,
                $request->user()->id
            )
        ],201);
    }

    public function allocate(
        Request $request,
        WelfareFund $fund
    ){
        $data=$request->validate([
            'amount'=>'required|numeric|min:0.01',
            'allocation_date'=>'required|date_format:Y-m-d',

            'source_type'=>
                'required|in:association_fund,donation,special_allocation,other',

            'source_reference'=>'nullable|string|max:150',
            'description'=>'nullable|string|max:5000',

            'source_transaction_id'=>
                'nullable|integer|exists:transactions,id'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Fund allocation added.',
            'data'=>$this->service->addAllocation(
                $fund,
                $data,
                $request->user()->id
            )
        ],201);
    }

    public function index(Request $request)
    {
        $data=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|string|max:40',
            'fund_id'=>'nullable|integer|exists:welfare_funds,id',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=WelfareRequest::query()
            ->with([
                'member:id,user_id,member_code',
                'member.user:id,name',
                'fund:id,code,name'
            ])
            ->when(
                $data['status']??null,
                fn($q,$v)=>$q->where(
                    'status',
                    $v
                )
            )
            ->when(
                $data['fund_id']??null,
                fn($q,$v)=>$q->where(
                    'welfare_fund_id',
                    $v
                )
            )
            ->when(
                $data['search']??null,
                function($q,$search){
                    $q->where(function($q)use($search){
                        $q->where(
                            'request_no',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'member.user',
                            fn($u)=>$u->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                        )
                        ->orWhereHas(
                            'member',
                            fn($m)=>$m->where(
                                'member_code',
                                'like',
                                "%{$search}%"
                            )
                        );
                    });
                }
            )
            ->latest('id');

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                $data['per_page']??15
            )
        ]);
    }

    public function options(Request $request)
    {
        $search=trim(
            (string)$request->query(
                'member_search',
                ''
            )
        );

        return response()->json([
            'success'=>true,
            'data'=>[
                'members'=>Member::query()
                    ->select([
                        'id',
                        'user_id',
                        'member_code'
                    ])
                    ->with('user:id,name')
                    ->where('status','active')
                    ->when(
                        $search,
                        fn($q)=>$q->where(function($q)use($search){
                            $q->where(
                                'member_code',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'user',
                                fn($u)=>$u->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                            );
                        })
                    )
                    ->limit(30)
                    ->get(),

                'funds'=>WelfareFund::where(
                    'is_active',
                    true
                )->get([
                    'id',
                    'code',
                    'name'
                ]),

                'cash_bank_accounts'=>Account::query()
                    ->active()
                    ->posting()
                    ->whereIn(
                        'sub_type',
                        ['cash','bank']
                    )
                    ->get([
                        'id',
                        'code',
                        'name'
                    ]),

                'expense_accounts'=>Account::query()
                    ->active()
                    ->posting()
                    ->where('type','expense')
                    ->get([
                        'id',
                        'code',
                        'name',
                        'sub_type'
                    ])
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data=$request->validate([
            'member_id'=>'required|integer|exists:members,id',

            'welfare_fund_id'=>
                'required|integer|exists:welfare_funds,id',

            'assistance_type'=>
                'required|in:illness,accident,death,natural_disaster,emergency,financial_hardship,other',

            'requested_amount'=>
                'required|numeric|min:0.01',

            'reason'=>'required|string|max:5000',
            'notes'=>'nullable|string|max:5000',
            'request_date'=>'nullable|date_format:Y-m-d'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Welfare request created.',
            'data'=>$this->service->createRequest(
                $data,
                $request->user()->id
            )
        ],201);
    }

    public function show(WelfareRequest $welfareRequest)
    {
        return response()->json([
            'success'=>true,
            'data'=>$welfareRequest->load([
                'fund.expenseAccount',
                'member.user',
                'paymentAccount',
                'financeTransaction.entries.account',
                'reversalTransaction.entries.account',
                'documents.uploader:id,name',
                'histories.user:id,name'
            ])
        ]);
    }

    public function review(
        Request $request,
        WelfareRequest $welfareRequest
    ){
        $data=$request->validate([
            'review_note'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Request moved to review.',
            'data'=>$this->service->review(
                $welfareRequest,
                $data['review_note']??null,
                $request->user()->id
            )
        ]);
    }

    public function approve(
        Request $request,
        WelfareRequest $welfareRequest
    ){
        $data=$request->validate([
            'approved_amount'=>
                'required|numeric|min:0.01'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Welfare request approved.',
            'data'=>$this->service->approve(
                $welfareRequest,
                (float)$data['approved_amount'],
                $request->user()->id
            )
        ]);
    }

    public function reject(
        Request $request,
        WelfareRequest $welfareRequest
    ){
        $data=$request->validate([
            'rejection_reason'=>
                'required|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Welfare request rejected.',
            'data'=>$this->service->reject(
                $welfareRequest,
                $data['rejection_reason'],
                $request->user()->id
            )
        ]);
    }

    public function disburse(
        Request $request,
        WelfareRequest $welfareRequest
    ){
        $data=$request->validate([
            'payment_account_id'=>
                'required|integer|exists:accounts,id',

            'disbursement_date'=>
                'required|date_format:Y-m-d'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Welfare assistance disbursed.',
            'data'=>$this->service->disburse(
                $welfareRequest,
                $data,
                $request->user()->id
            )
        ]);
    }

    public function cancel(
        Request $request,
        WelfareRequest $welfareRequest
    ){
        return response()->json([
            'success'=>true,
            'message'=>'Welfare request cancelled.',
            'data'=>$this->service->cancel(
                $welfareRequest,
                $request->user()->id
            )
        ]);
    }

    public function reverse(
        Request $request,
        WelfareRequest $welfareRequest
    ){
        $data=$request->validate([
            'reason'=>'required|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Welfare disbursement reversed.',
            'data'=>$this->service->reverse(
                $welfareRequest,
                $data['reason'],
                $request->user()->id
            )
        ]);
    }

    public function document(
        WelfareDocument $document
    ){
        abort_unless(
            Storage::disk('local')
                ->exists($document->file_path),
            404
        );

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name
        );
    }
}