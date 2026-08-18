<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TellerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TellerController extends Controller
{
    public function __construct(
        private TellerService $tellerService
    ) {}

    public function receive(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['nullable', 'exists:members,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'transaction_date' => ['nullable', 'date'],
        ]);

        $transaction = $this->tellerService->receive(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Cash received successfully.',
            'data' => $transaction->load('member'),
        ], 201);
    }

    public function payment(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['nullable', 'exists:members,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'transaction_date' => ['nullable', 'date'],
        ]);

        $balance = $this->tellerService->balance(
            $request->user()->id
        );

        if ($validated['amount'] > $balance['balance']) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient teller balance.',
            ]);
        }

        $transaction = $this->tellerService->payment(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Cash payment successfully recorded.',
            'data' => $transaction->load('member'),
        ], 201);
    }

    public function balance(Request $request)
    {
        return response()->json([
            'data' => $this->tellerService->balance(
                $request->user()->id
            ),
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = $request->user()
            ->tellerTransactions()
            ->with('member')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $transactions,
        ]);
    }
}