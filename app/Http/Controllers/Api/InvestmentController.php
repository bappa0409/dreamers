<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Services\InvestmentService;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    public function __construct(
        protected InvestmentService $investmentService
    ) {
    }

    public function index()
    {
        $investments = Investment::with('member.user')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $investments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'expected_return' => 'nullable|numeric|min:0',
            'investment_date' => 'required|date',
            'maturity_date' => 'nullable|date|after_or_equal:investment_date',
            'status' => 'nullable|in:pending,active,completed,cancelled',
        ]);

        $investment = $this->investmentService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Investment created successfully.',
            'data' => $investment->load('member.user'),
        ], 201);
    }

    public function show(Investment $investment)
    {
        return response()->json([
            'success' => true,
            'data' => $investment->load([
                'member.user',
                'returns',
            ]),
        ]);
    }

    public function update(Request $request, Investment $investment)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'expected_return' => 'nullable|numeric|min:0',
            'investment_date' => 'sometimes|required|date',
            'maturity_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,active,completed,cancelled',
        ]);

        $investment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Investment updated successfully.',
            'data' => $investment->fresh()->load('member.user'),
        ]);
    }

    public function destroy(Investment $investment)
    {
        $investment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Investment deleted successfully.',
        ]);
    }
}