<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TellerClosing;
use App\Models\TellerTransaction;
use Illuminate\Http\Request;

class TellerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tellerId = $request->user()->id;
        $today = now()->toDateString();

        $todayReceived = TellerTransaction::where('teller_id', $tellerId)
            ->whereDate('transaction_date', $today)
            ->where('type', 'receive')
            ->where('status', 'completed')
            ->sum('amount');

        $todayPaid = TellerTransaction::where('teller_id', $tellerId)
            ->whereDate('transaction_date', $today)
            ->where('type', 'payment')
            ->where('status', 'completed')
            ->sum('amount');

        $todayTransactions = TellerTransaction::where('teller_id', $tellerId)
            ->whereDate('transaction_date', $today)
            ->where('status', 'completed')
            ->count();

        $previousClosing = TellerClosing::where('teller_id', $tellerId)
            ->where('status', 'closed')
            ->where('closing_date', '<', $today)
            ->latest('closing_date')
            ->first();

        $openingBalance = $previousClosing
            ? (float) $previousClosing->actual_balance
            : 0;

        $expectedBalance =
            $openingBalance +
            (float) $todayReceived -
            (float) $todayPaid;

        $todayClosing = TellerClosing::where('teller_id', $tellerId)
            ->where('closing_date', $today)
            ->first();

        $recentTransactions = TellerTransaction::where(
            'teller_id',
            $tellerId
        )
            ->with('member')
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'date' => $today,

                'opening_balance' => $openingBalance,

                'today_received' => (float) $todayReceived,

                'today_paid' => (float) $todayPaid,

                'expected_balance' => $expectedBalance,

                'actual_balance' => $todayClosing
                    ? (float) $todayClosing->actual_balance
                    : null,

                'difference' => $todayClosing
                    ? (float) $todayClosing->difference
                    : null,

                'closing_status' => $todayClosing
                    ? $todayClosing->status
                    : 'open',

                'today_transaction_count' => $todayTransactions,

                'recent_transactions' => $recentTransactions,
            ],
        ]);
    }
}