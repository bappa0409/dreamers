<?php

namespace App\Services;

use App\Models\TellerClosing;
use App\Models\TellerTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TellerClosingService
{
    public function getTodaySummary(int $tellerId): array
    {
        $date = now()->toDateString();

        $previousClosing = TellerClosing::where('teller_id', $tellerId)
            ->where('status', 'closed')
            ->where('closing_date', '<', $date)
            ->latest('closing_date')
            ->first();

        $openingBalance = $previousClosing
            ? (float) $previousClosing->actual_balance
            : 0;

        $received = TellerTransaction::where('teller_id', $tellerId)
            ->whereDate('transaction_date', $date)
            ->where('type', 'receive')
            ->where('status', 'completed')
            ->sum('amount');

        $paid = TellerTransaction::where('teller_id', $tellerId)
            ->whereDate('transaction_date', $date)
            ->where('type', 'payment')
            ->where('status', 'completed')
            ->sum('amount');

        $expectedBalance =
            $openingBalance +
            (float) $received -
            (float) $paid;

        return [
            'date' => $date,
            'opening_balance' => $openingBalance,
            'total_received' => (float) $received,
            'total_paid' => (float) $paid,
            'expected_balance' => $expectedBalance,
        ];
    }

    public function close(
        int $tellerId,
        float $actualBalance,
        ?string $notes = null
    ): TellerClosing {

        return DB::transaction(function () use (
            $tellerId,
            $actualBalance,
            $notes
        ) {

            $date = now()->toDateString();

            $existing = TellerClosing::where('teller_id', $tellerId)
                ->where('closing_date', $date)
                ->first();

            if ($existing && $existing->status === 'closed') {
                throw ValidationException::withMessages([
                    'closing' => 'Teller is already closed for today.',
                ]);
            }

            $summary = $this->getTodaySummary($tellerId);

            $difference =
                $actualBalance -
                $summary['expected_balance'];

            $closing = TellerClosing::updateOrCreate(
                [
                    'teller_id' => $tellerId,
                    'closing_date' => $date,
                ],
                [
                    'opening_balance' => $summary['opening_balance'],
                    'total_received' => $summary['total_received'],
                    'total_paid' => $summary['total_paid'],
                    'expected_balance' => $summary['expected_balance'],
                    'actual_balance' => $actualBalance,
                    'difference' => $difference,
                    'status' => 'closed',
                    'notes' => $notes,
                    'closed_at' => now(),
                    'closed_by' => $tellerId,
                ]
            );

            return $closing;
        });
    }

    public function reopen(
        int $tellerId,
        string $date
    ): TellerClosing {

        $closing = TellerClosing::where('teller_id', $tellerId)
            ->where('closing_date', $date)
            ->first();

        if (!$closing) {
            throw ValidationException::withMessages([
                'closing' => 'Closing record not found.',
            ]);
        }

        $closing->update([
            'status' => 'reopened',
            'closed_at' => null,
        ]);

        return $closing->fresh();
    }
}