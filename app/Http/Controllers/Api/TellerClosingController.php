<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TellerClosingService;
use Illuminate\Http\Request;

class TellerClosingController extends Controller
{
    public function __construct(
        private TellerClosingService $closingService
    ) {}

    public function summary(Request $request)
    {
        return response()->json([
            'data' => $this->closingService->getTodaySummary(
                $request->user()->id
            ),
        ]);
    }

    public function close(Request $request)
    {
        $validated = $request->validate([
            'actual_balance' => [
                'required',
                'numeric',
                'min:0'
            ],
            'notes' => [
                'nullable',
                'string'
            ],
        ]);

        $closing = $this->closingService->close(
            $request->user()->id,
            (float) $validated['actual_balance'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'message' => 'Teller closed successfully.',
            'data' => $closing,
        ]);
    }

    public function reopen(
        Request $request,
        string $date
    ) {
        $closing = $this->closingService->reopen(
            $request->user()->id,
            $date
        );

        return response()->json([
            'message' => 'Teller closing reopened successfully.',
            'data' => $closing,
        ]);
    }
}