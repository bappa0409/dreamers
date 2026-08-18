<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {
    }

    public function index()
    {
        $projects = Project::withCount('members')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|in:planned,active,on_hold,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $project = $this->projectService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully.',
            'data' => $project,
        ], 201);
    }

    public function show(Project $project)
    {
        return response()->json([
            'success' => true,
            'data' => $project->load('members.member.user'),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|in:planned,active,on_hold,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully.',
            'data' => $project->fresh(),
        ]);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully.',
        ]);
    }
}