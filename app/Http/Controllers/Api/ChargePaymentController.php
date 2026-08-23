<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChargePayment;
use Illuminate\Http\Request;

class ChargePaymentController extends Controller
{
    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:posted,cancelled',
            'payment_method'=>'nullable|in:cash,bank,mobile_banking,online',
            'member_charge_id'=>
                'nullable|integer|exists:member_charges,id',
            'from'=>'nullable|date_format:Y-m-d',
            'to'=>'nullable|date_format:Y-m-d|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=ChargePayment::query()
            ->with([
                'charge:id,charge_no,member_id,amount,paid_amount,status',
                'charge.member:id,user_id,member_code',
                'charge.member.user:id,name,email,mobile',
                'receiveAccount:id,code,name,sub_type',
                'creator:id,name',
            ])
            ->when(
                !empty($validated['search']),
                function($query)use($validated){
                    $search=trim(
                        $validated['search']
                    );

                    $query->where(function($q)use($search){
                        $q->where(
                            'payment_no',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'charge',
                            function($charge)use($search){
                                $charge
                                    ->where(
                                        'charge_no',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhereHas(
                                        'member',
                                        function($member)use($search){
                                            $member
                                                ->where(
                                                    'member_code',
                                                    'like',
                                                    "%{$search}%"
                                                )
                                                ->orWhereHas(
                                                    'user',
                                                    function($user)use($search){
                                                        $user
                                                            ->where(
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
                                        }
                                    );
                            }
                        );
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
                !empty($validated['payment_method']),
                fn($q)=>$q->where(
                    'payment_method',
                    $validated['payment_method']
                )
            )
            ->when(
                !empty($validated['member_charge_id']),
                fn($q)=>$q->where(
                    'member_charge_id',
                    $validated['member_charge_id']
                )
            )
            ->when(
                !empty($validated['from']),
                fn($q)=>$q->whereDate(
                    'payment_date',
                    '>=',
                    $validated['from']
                )
            )
            ->when(
                !empty($validated['to']),
                fn($q)=>$q->whereDate(
                    'payment_date',
                    '<=',
                    $validated['to']
                )
            )
            ->latest('payment_date')
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
        $summary=ChargePayment::query()
            ->selectRaw("
                COUNT(*) as payment_count,
                COALESCE(SUM(
                    CASE
                        WHEN status='posted'
                        THEN amount
                        ELSE 0
                    END
                ),0) as total_received
            ")
            ->first();

        return response()->json([
            'success'=>true,
            'data'=>[
                'payment_count'=>
                    (int)($summary?->payment_count??0),

                'total_received'=>round(
                    (float)($summary?->total_received??0),
                    2
                ),
            ],
        ]);
    }

    public function show(
        ChargePayment $chargePayment
    ){
        return response()->json([
            'success'=>true,
            'data'=>$chargePayment->load([
                'charge.member.user:id,name,email,mobile',
                'charge.incomeAccount:id,code,name',
                'receiveAccount:id,code,name',
                'creator:id,name,email',
                'financeTransaction.entries.account',
            ]),
        ]);
    }
}