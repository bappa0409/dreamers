<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TellerTransaction;
use App\Services\TellerService;
use Illuminate\Http\Request;

class TellerController extends Controller
{
    public function __construct(
        protected TellerService $tellerService
    ){}

    public function options()
    {
        return response()->json([
            'data'=>$this->tellerService->options()
        ]);
    }

    public function receive(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>[
                'nullable',
                'exists:members,id'
            ],
            'cash_account_id'=>[
                'required',
                'exists:accounts,id'
            ],
            'counter_account_id'=>[
                'required',
                'exists:accounts,id'
            ],
            'amount'=>[
                'required',
                'numeric',
                'min:0.01'
            ],
            'purpose'=>[
                'nullable',
                'string',
                'max:255'
            ],
            'description'=>[
                'nullable',
                'string',
                'max:5000'
            ],
            'transaction_date'=>[
                'nullable',
                'date'
            ]
        ]);

        $transaction=$this
            ->tellerService
            ->receive(
                $validated,
                $request->user()->id
            );

        return response()->json([
            'message'=>
                'Cash received and accounting entry posted successfully.',
            'data'=>$transaction
        ],201);
    }

    public function payment(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>[
                'nullable',
                'exists:members,id'
            ],
            'cash_account_id'=>[
                'required',
                'exists:accounts,id'
            ],
            'counter_account_id'=>[
                'required',
                'exists:accounts,id'
            ],
            'amount'=>[
                'required',
                'numeric',
                'min:0.01'
            ],
            'purpose'=>[
                'nullable',
                'string',
                'max:255'
            ],
            'description'=>[
                'nullable',
                'string',
                'max:5000'
            ],
            'transaction_date'=>[
                'nullable',
                'date'
            ]
        ]);

        $transaction=$this
            ->tellerService
            ->payment(
                $validated,
                $request->user()->id
            );

        return response()->json([
            'message'=>
                'Cash payment and accounting entry posted successfully.',
            'data'=>$transaction
        ],201);
    }

    public function balance(Request $request)
    {
        $validated=$request->validate([
            'cash_account_id'=>[
                'nullable',
                'integer',
                'exists:accounts,id'
            ]
        ]);

        return response()->json([
            'data'=>$this
                ->tellerService
                ->balance(
                    $request->user()->id,
                    $validated['cash_account_id']
                        ??null
                )
        ]);
    }

    public function transactions(Request $request)
    {
        $validated=$request->validate([
            'type'=>[
                'nullable',
                'in:receive,payment'
            ],
            'status'=>[
                'nullable',
                'in:pending,completed,cancelled'
            ],
            'from'=>[
                'nullable',
                'date'
            ],
            'to'=>[
                'nullable',
                'date',
                'after_or_equal:from'
            ],
            'per_page'=>[
                'nullable',
                'integer',
                'min:5',
                'max:100'
            ]
        ]);

        $query=TellerTransaction::query()
            ->where(
                'teller_id',
                $request->user()->id
            )
            ->with([
                'member.user',
                'cashAccount:id,code,name',
                'counterAccount:id,code,name',
                'financeTransaction.entries.account'
            ])
            ->latest('transaction_date')
            ->latest('id');

        if(!empty($validated['type'])){
            $query->where(
                'type',
                $validated['type']
            );
        }

        if(!empty($validated['status'])){
            $query->where(
                'status',
                $validated['status']
            );
        }

        if(!empty($validated['from'])){
            $query->whereDate(
                'transaction_date',
                '>=',
                $validated['from']
            );
        }

        if(!empty($validated['to'])){
            $query->whereDate(
                'transaction_date',
                '<=',
                $validated['to']
            );
        }

        return response()->json([
            'data'=>$query->paginate(
                $validated['per_page']??20
            )
        ]);
    }

    public function cancel(
        Request $request,
        TellerTransaction $tellerTransaction
    ){
        if(
            (int)$tellerTransaction->teller_id!==
            (int)$request->user()->id
        ){
            abort(403);
        }

        $transaction=$this
            ->tellerService
            ->cancel(
                $tellerTransaction,
                $request->user()->id
            );

        return response()->json([
            'message'=>
                'Teller transaction cancelled and accounting journal reversed successfully.',
            'data'=>$transaction
        ]);
    }
}