<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Land;
use App\Services\LandService;
use Illuminate\Http\Request;

class LandController extends Controller
{
    public function __construct(
        protected LandService $landService
    ) {
    }

    public function index()
    {
        $lands = Land::withCount('investments')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $lands,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'district' => 'nullable|string|max:100',
            'upazila' => 'nullable|string|max:100',
            'mouza' => 'nullable|string|max:100',
            'khatian_no' => 'nullable|string|max:100',
            'dag_no' => 'nullable|string|max:100',
            'land_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|string|max:30',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'seller_name' => 'nullable|string|max:255',
            'seller_phone' => 'nullable|string|max:30',
            'status' => 'nullable|in:planned,negotiating,purchased,sold,cancelled',
            'notes' => 'nullable|string',
        ]);

        $land = $this->landService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Land created successfully.',
            'data' => $land,
        ], 201);
    }

    public function show(Land $land)
    {
        return response()->json([
            'success' => true,
            'data' => $land->load([
                'investments.member.user',
                'documents',
            ]),
        ]);
    }

    public function update(Request $request, Land $land)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'district' => 'nullable|string|max:100',
            'upazila' => 'nullable|string|max:100',
            'mouza' => 'nullable|string|max:100',
            'khatian_no' => 'nullable|string|max:100',
            'dag_no' => 'nullable|string|max:100',
            'land_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|string|max:30',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'seller_name' => 'nullable|string|max:255',
            'seller_phone' => 'nullable|string|max:30',
            'status' => 'sometimes|in:planned,negotiating,purchased,sold,cancelled',
            'notes' => 'nullable|string',
        ]);

        $land->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Land updated successfully.',
            'data' => $land->fresh(),
        ]);
    }

    public function destroy(Land $land)
    {
        $land->delete();

        return response()->json([
            'success' => true,
            'message' => 'Land deleted successfully.',
        ]);
    }
}