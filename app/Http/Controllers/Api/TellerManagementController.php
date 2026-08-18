<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TellerClosing;
use App\Models\TellerTransaction;
use Illuminate\Http\Request;

class TellerManagementController extends Controller
{
    public function index()
    {
        $tellers = User::whereHas('role', function ($query) {
            $query->where('name', 'teller');
        })
        ->with('role')
        ->withCount('tellerTransactions')
        ->latest()
        ->paginate(20);

        return response()->json([
            'data' => $tellers,
        ]);
    }

    public function show(User $user)
    {
        if (!$user->hasRole('teller')) {
            return response()->json([
                'message' => 'The selected user is not a teller.',
            ], 422);
        }

        return response()->json([
            'data' => $user->load('role'),
        ]);
    }

    public function transactions(User $user)
    {
        if (!$user->hasRole('teller')) {
            return response()->json([
                'message' => 'The selected user is not a teller.',
            ], 422);
        }

        $transactions = TellerTransaction::where(
            'teller_id',
            $user->id
        )
        ->with('member', 'financeTransaction')
        ->latest()
        ->paginate(20);

        return response()->json([
            'data' => $transactions,
        ]);
    }

    public function closings(User $user)
    {
        if (!$user->hasRole('teller')) {
            return response()->json([
                'message' => 'The selected user is not a teller.',
            ], 422);
        }

        $closings = TellerClosing::where(
            'teller_id',
            $user->id
        )
        ->with('closedBy')
        ->latest('closing_date')
        ->paginate(20);

        return response()->json([
            'data' => $closings,
        ]);
    }

    public function toggleStatus(User $user)
    {
        if (!$user->hasRole('teller')) {
            return response()->json([
                'message' => 'The selected user is not a teller.',
            ], 422);
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return response()->json([
            'message' => $user->is_active
                ? 'Teller activated successfully.'
                : 'Teller deactivated successfully.',
            'data' => $user->fresh(),
        ]);
    }
}