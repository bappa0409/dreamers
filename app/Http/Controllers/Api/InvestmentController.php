<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Investment;
use App\Models\InvestmentReturn;
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
            'from'=>'nullable|date_format:Y-m-d',
            'to'=>'nullable|date_format:Y-m-d|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=Investment::query()
            ->select([
                'id',
                'investment_no',
                'payment_account_id',
                'title',
                'description',
                'amount',
                'expected_return',
                'investment_date',
                'maturity_date',
                'status',
                'finance_transaction_id',
            ])
            ->with([
                'paymentAccount:id,code,name,sub_type',
            ])
            ->withSum([
                'returns as paid_return_total'=>fn($q)=>
                    $q->where('status','paid')
                        ->where('return_type','income'),
            ],'amount')
            ->withSum([
                'returns as principal_return_total'=>fn($q)=>
                    $q->where('status','paid')
                        ->where('return_type','principal'),
            ],'amount')
            ->when(
                !empty($validated['status']),
                fn($q)=>$q->where(
                    'status',
                    $validated['status']
                )
            )
            ->when(
                !empty($validated['from']),
                fn($q)=>$q->whereDate(
                    'investment_date',
                    '>=',
                    $validated['from']
                )
            )
            ->when(
                !empty($validated['to']),
                fn($q)=>$q->whereDate(
                    'investment_date',
                    '<=',
                    $validated['to']
                )
            )
            ->when(
                !empty($validated['search']),
                function($q)use($validated){
                    $search=trim($validated['search']);

                    $q->where(function($query)use($search){
                        $query
                            ->where(
                                'investment_no',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'title',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'description',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->latest('investment_date')
            ->latest('id');

        $perPage=min(
            max(
                (int)($validated['per_page']??15),
                5
            ),
            100
        );

        return response()->json([
            'success'=>true,
            'data'=>$query
                ->paginate($perPage)
                ->withQueryString(),
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->investmentService
                ->statistics(),
        ]);
    }

    public function options()
    {
        $accounts=Account::query()
            ->active()
            ->where('type','asset')
            ->whereIn(
                'sub_type',
                ['cash','bank']
            )
            ->whereDoesntHave('children')
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
                'sub_type',
            ]);

        return response()->json([
            'success'=>true,
            'data'=>[
                'cash_bank_accounts'=>$accounts,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'payment_account_id'=>
                'required|integer|exists:accounts,id',

            'title'=>
                'required|string|max:150',

            'description'=>
                'nullable|string|max:5000',

            'amount'=>
                'required|numeric|min:0.01|max:9999999999999.99',

            'expected_return'=>
                'nullable|numeric|min:0|max:9999999999999.99',

            'investment_date'=>
                'required|date_format:Y-m-d',

            'maturity_date'=>
                'nullable|date_format:Y-m-d|after_or_equal:investment_date',

            'status'=>
                'nullable|in:pending,active',
        ]);

        $investment=$this->investmentService
            ->create(
                $validated,
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>'Investment created successfully.',
            'data'=>$investment,
        ],201);
    }

    public function show(Investment $investment)
    {
        $investment->load([
            'paymentAccount',
            'financeTransaction.entries.account',

            'returns'=>fn($q)=>
                $q->with([
                    'receiveAccount',
                    'financeTransaction.entries.account',
                ])
                ->latest('return_date')
                ->latest('id'),
        ]);

        $investment->loadSum([
            'returns as paid_return_total'=>fn($q)=>
                $q->where('status','paid')
                    ->where('return_type','income'),
        ],'amount');

        $investment->loadSum([
            'returns as principal_return_total'=>fn($q)=>
                $q->where('status','paid')
                    ->where('return_type','principal'),
        ],'amount');

        return response()->json([
            'success'=>true,
            'data'=>$investment,
        ]);
    }

    public function update(
        Request $request,
        Investment $investment
    ){
        $validated=$request->validate([
            'payment_account_id'=>
                'sometimes|required|integer|exists:accounts,id',

            'title'=>
                'sometimes|required|string|max:150',

            'description'=>
                'nullable|string|max:5000',

            'amount'=>
                'sometimes|required|numeric|min:0.01|max:9999999999999.99',

            'expected_return'=>
                'nullable|numeric|min:0|max:9999999999999.99',

            'investment_date'=>
                'sometimes|required|date_format:Y-m-d',

            'maturity_date'=>
                'nullable|date_format:Y-m-d',

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            |
            | completed is system-managed.
            | cancelled must use cancel().
            |
            */
            'status'=>
                'sometimes|required|in:pending,active',
        ]);

        if(!empty($validated['maturity_date'])){
            $investmentDate=
                $validated['investment_date']
                ??$investment->investment_date
                    ->toDateString();

            if(
                $validated['maturity_date']<
                $investmentDate
            ){
                return response()->json([
                    'message'=>
                        'The maturity date must be on or after the investment date.',

                    'errors'=>[
                        'maturity_date'=>[
                            'The maturity date must be on or after the investment date.',
                        ],
                    ],
                ],422);
            }
        }

        $investment=$this->investmentService
            ->update(
                $investment,
                $validated
            );

        return response()->json([
            'success'=>true,
            'message'=>'Investment updated successfully.',
            'data'=>$investment,
        ]);
    }

    public function cancel(
        Request $request,
        Investment $investment
    ){
        $investment=$this->investmentService
            ->cancel(
                $investment,
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>'Investment cancelled successfully.',
            'data'=>$investment,
        ]);
    }

    public function destroy(
        Investment $investment
    ){
        $this->investmentService
            ->delete($investment);

        return response()->json([
            'success'=>true,
            'message'=>'Investment deleted successfully.',
        ]);
    }

    public function storeReturn(
        Request $request,
        Investment $investment
    ){
        $validated=$request->validate([
            'return_type'=>
                'required|in:income,principal',

            'receive_account_id'=>
                'nullable|integer|exists:accounts,id',

            'amount'=>
                'required|numeric|min:0.01|max:9999999999999.99',

            'return_date'=>
                'required|date_format:Y-m-d',

            'description'=>
                'nullable|string|max:3000',

            'status'=>
                'required|in:pending,paid',
        ]);

        $return=$this->investmentService
            ->addReturn(
                $investment,
                $validated,
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>$return->status==='paid'
                ?'Investment return added and accounting entry posted successfully.'
                :'Investment return added successfully.',
            'data'=>$return,
        ],201);
    }

    public function updateReturn(
        Request $request,
        InvestmentReturn $investmentReturn
    ){
        $validated=$request->validate([
            'return_type'=>
                'sometimes|required|in:income,principal',

            'receive_account_id'=>
                'nullable|integer|exists:accounts,id',

            'amount'=>
                'sometimes|required|numeric|min:0.01|max:9999999999999.99',

            'return_date'=>
                'sometimes|required|date_format:Y-m-d',

            'description'=>
                'nullable|string|max:3000',

            'status'=>
                'sometimes|required|in:pending,paid,cancelled',
        ]);

        $return=$this->investmentService
            ->updateReturn(
                $investmentReturn,
                $validated,
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>'Investment return updated successfully.',
            'data'=>$return,
        ]);
    }

    public function destroyReturn(
        InvestmentReturn $investmentReturn
    ){
        $this->investmentService
            ->deleteReturn(
                $investmentReturn
            );

        return response()->json([
            'success'=>true,
            'message'=>'Investment return deleted successfully.',
        ]);
    }
}