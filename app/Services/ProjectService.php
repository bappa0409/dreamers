<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {

            $lastProject = Project::latest('id')->first();

            $nextNumber = $lastProject
                ? $lastProject->id + 1
                : 1;

            $projectCode = 'PROJ-' . str_pad(
                $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );

            return Project::create([
                'project_code' => $projectCode,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null,
                'budget' => $data['budget'] ?? 0,
                'actual_cost' => $data['actual_cost'] ?? 0,
                'start_date' => $data['start_date'] ?? null,
                'expected_end_date' => $data['expected_end_date'] ?? null,
                'actual_end_date' => $data['actual_end_date'] ?? null,
                'progress' => $data['progress'] ?? 0,
                'status' => $data['status'] ?? 'planned',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}