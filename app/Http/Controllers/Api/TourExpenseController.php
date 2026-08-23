<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourExpense;
use App\Services\TourService;
use Illuminate\Http\Request;

class TourExpenseController extends Controller
{
    public function __construct(protected TourService $tourService){}

    public function store(Request $request,Tour $tour)
    {
        $data=$request->validate([
            'category'=>'required|string|max:100',
            'expense_account_id'=>'required|integer|exists:accounts,id',
            'payment_account_id'=>'required|integer|exists:accounts,id',
            'amount'=>'required|numeric|min:0.01|max:9999999999999.99',
            'expense_date'=>'required|date',
            'payee'=>'nullable|string|max:255',
            'reference_no'=>'nullable|string|max:150',
            'description'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Tour expense posted successfully.',
            'data'=>$this->tourService->addExpense(
                $tour,
                $data,
                $request->user()->id
            )
        ],201);
    }

    public function cancel(Request $request,TourExpense $expense)
    {
        return response()->json([
            'success'=>true,
            'message'=>'Tour expense cancelled and accounting entry reversed.',
            'data'=>$this->tourService->cancelExpense(
                $expense,
                $request->user()->id
            )
        ]);
    }
}