<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'type'=>'nullable|in:income,expense,deposit,withdrawal,transfer,adjustment',
            'status'=>'nullable|in:draft,posted,cancelled',
            'from'=>'nullable|date',
            'to'=>'nullable|date|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=Transaction::query()
            ->with([
                'creator:id,name,email',
                'entries.account:id,code,name,type'
            ])
            ->withSum('entries as total_debit','debit')
            ->withSum('entries as total_credit','credit')
            ->latest('transaction_date')
            ->latest('id');

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('transaction_no','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhere('reference_type','like',"%{$search}%")
                    ->orWhereHas('creator',function($uq)use($search){
                        $uq->where('name','like',"%{$search}%")
                            ->orWhere('email','like',"%{$search}%");
                    })
                    ->orWhereHas('entries.account',function($aq)use($search){
                        $aq->where('name','like',"%{$search}%")
                            ->orWhere('code','like',"%{$search}%");
                    });
            });
        }

        if(!empty($validated['type'])){
            $query->where('type',$validated['type']);
        }

        if(!empty($validated['status'])){
            $query->where('status',$validated['status']);
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
            'success'=>true,
            'data'=>$query->paginate(
                min((int)($validated['per_page']??15),100)
            )
        ]);
    }

    public function show(Transaction $transaction)
    {
        return response()->json([
            'success'=>true,
            'data'=>$transaction->load([
                'creator:id,name,email',
                'entries.account:id,code,name,type'
            ])->loadSum('entries as total_debit','debit')
                ->loadSum('entries as total_credit','credit')
        ]);
    }
}