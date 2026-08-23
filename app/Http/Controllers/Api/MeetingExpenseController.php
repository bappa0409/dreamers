<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingExpense;
use App\Services\MeetingService;
use Illuminate\Http\Request;

class MeetingExpenseController extends Controller
{
    public function __construct(
        protected MeetingService $meetingService
    ){}

    public function store(Request $request,Meeting $meeting)
    {
        $data=$request->validate([
            'category'=>'required|string|max:100',
            'expense_account_id'=>'required|exists:accounts,id',
            'payment_account_id'=>'required|exists:accounts,id',
            'amount'=>'required|numeric|min:0.01',
            'expense_date'=>'required|date',
            'payee'=>'nullable|string|max:255',
            'reference_no'=>'nullable|string|max:150',
            'description'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Meeting expense posted.',
            'data'=>$this->meetingService->addExpense(
                $meeting,
                $data,
                $request->user()->id
            )
        ],201);
    }

    public function cancel(Request $request,MeetingExpense $expense)
    {
        return response()->json([
            'success'=>true,
            'message'=>'Meeting expense reversed.',
            'data'=>$this->meetingService->cancelExpense(
                $expense,
                $request->user()->id
            )
        ]);
    }
}