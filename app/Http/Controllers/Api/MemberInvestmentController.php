<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Http\Request;

class MemberInvestmentController extends Controller
{
    public function index(Request $request)
    {
        $validated=$request->validate([
            'status'=>'nullable|in:active,completed',
            'search'=>'nullable|string|max:150',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=Investment::query()
            ->select([
                'id',
                'investment_no',
                'title',
                'description',
                'amount',
                'expected_return',
                'investment_date',
                'maturity_date',
                'status',
            ])
            ->withSum([
                'returns as income_received'=>fn($q)=>
                    $q->where('status','paid')
                        ->where('return_type','income'),
            ],'amount')
            ->withSum([
                'returns as principal_returned'=>fn($q)=>
                    $q->where('status','paid')
                        ->where('return_type','principal'),
            ],'amount')
            ->whereIn(
                'status',
                ['active','completed']
            )
            ->when(
                !empty($validated['status']),
                fn($q)=>$q->where(
                    'status',
                    $validated['status']
                )
            )
            ->when(
                !empty($validated['search']),
                function($q)use($validated){
                    $search=trim(
                        $validated['search']
                    );

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

        $investments=$query
            ->paginate($perPage)
            ->withQueryString();

        $investments->getCollection()->transform(
            function(Investment $investment){
                $income=round(
                    (float)($investment->income_received??0),
                    2
                );

                $principal=round(
                    (float)($investment->principal_returned??0),
                    2
                );

                $amount=round(
                    (float)$investment->amount,
                    2
                );

                return[
                    'id'=>$investment->id,
                    'investment_no'=>$investment->investment_no,
                    'title'=>$investment->title,
                    'description'=>$investment->description,
                    'amount'=>$amount,
                    'expected_return'=>round(
                        (float)$investment->expected_return,
                        2
                    ),
                    'investment_date'=>$investment
                        ->investment_date
                        ?->toDateString(),
                    'maturity_date'=>$investment
                        ->maturity_date
                        ?->toDateString(),
                    'status'=>$investment->status,
                    'income_received'=>$income,
                    'principal_returned'=>$principal,
                    'remaining_principal'=>max(
                        round(
                            $amount-$principal,
                            2
                        ),
                        0
                    ),
                ];
            }
        );

        return response()->json([
            'success'=>true,
            'data'=>$investments,
        ]);
    }

    public function show(
        Request $request,
        Investment $investment
    ){
        abort_unless(
            in_array(
                $investment->status,
                ['active','completed'],
                true
            ),
            404
        );

        $investment->load([
            'returns'=>fn($q)=>
                $q->where('status','paid')
                    ->select([
                        'id',
                        'investment_id',
                        'return_type',
                        'amount',
                        'return_date',
                        'description',
                        'status',
                    ])
                    ->latest('return_date')
                    ->latest('id'),
        ]);

        $incomeReceived=round(
            (float)$investment
                ->returns
                ->where('return_type','income')
                ->sum('amount'),
            2
        );

        $principalReturned=round(
            (float)$investment
                ->returns
                ->where('return_type','principal')
                ->sum('amount'),
            2
        );

        $amount=round(
            (float)$investment->amount,
            2
        );

        return response()->json([
            'success'=>true,
            'data'=>[
                'id'=>$investment->id,
                'investment_no'=>$investment->investment_no,
                'title'=>$investment->title,
                'description'=>$investment->description,
                'amount'=>$amount,
                'expected_return'=>round(
                    (float)$investment->expected_return,
                    2
                ),
                'investment_date'=>$investment
                    ->investment_date
                    ?->toDateString(),
                'maturity_date'=>$investment
                    ->maturity_date
                    ?->toDateString(),
                'status'=>$investment->status,

                'summary'=>[
                    'income_received'=>$incomeReceived,
                    'principal_returned'=>$principalReturned,
                    'remaining_principal'=>max(
                        round(
                            $amount-$principalReturned,
                            2
                        ),
                        0
                    ),
                ],

                'returns'=>$investment
                    ->returns
                    ->map(fn($return)=>[
                        'id'=>$return->id,
                        'return_type'=>$return->return_type,
                        'amount'=>round(
                            (float)$return->amount,
                            2
                        ),
                        'return_date'=>$return
                            ->return_date
                            ?->toDateString(),
                        'description'=>$return->description,
                    ])
                    ->values(),
            ],
        ]);
    }
}