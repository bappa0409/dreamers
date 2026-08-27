<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected NumberSequenceService $numberSequenceService
    ){}

    public function create(array $data): Project
    {
        return DB::transaction(function()use($data){
            $this->validateProjectDates(null,$data);

            $project=Project::create([
                ...$data,
                'project_code'=>$this->generateProjectCode(),
                'budget'=>$data['budget']??0,
                'actual_cost'=>$data['actual_cost']??0,
                'progress'=>$data['progress']??0,
                'status'=>$data['status']??'planned'
            ]);

            $this->forgetCaches();

            return $project;
        });
    }

    public function update(Project $project,array $data): Project
    {
        return DB::transaction(function()use($project,$data){
            if(isset($data['progress'])){
                $data['progress']=min(
                    max((int)$data['progress'],0),
                    100
                );
            }

            if(
                isset($data['status'])&&
                $data['status']==='completed'
            ){
                $data['progress']=100;
                $data['actual_end_date']=$data['actual_end_date']
                    ??now()->toDateString();
            }

            if(
                isset($data['progress'])&&
                (int)$data['progress']===100&&
                ($data['status']??$project->status)!=='cancelled'
            ){
                $data['status']='completed';
                $data['actual_end_date']=$data['actual_end_date']
                    ??now()->toDateString();
            }

            $this->validateProjectDates($project,$data);

            $project->update($data);

            $this->forgetCaches();

            return $project->fresh();
        });
    }

    public function delete(Project $project): void
    {
        DB::transaction(function()use($project){
            $project->delete();
            $this->forgetCaches();
        });
    }

    public function addMember(
        Project $project,
        array $data
    ): ProjectMember{
        return DB::transaction(function()use($project,$data){
            if($project->status==='cancelled'){
                throw ValidationException::withMessages([
                    'project'=>[
                        'Member cannot be added to a cancelled project.'
                    ]
                ]);
            }

            $member=Member::query()
                ->sharedLock()
                ->find($data['member_id']);

            if(!$member||$member->status!=='active'){
                throw ValidationException::withMessages([
                    'member_id'=>[
                        'Only an active member can be assigned to a project.'
                    ]
                ]);
            }

            $exists=$project->members()
                ->where('member_id',$data['member_id'])
                ->exists();

            if($exists){
                throw ValidationException::withMessages([
                    'member_id'=>[
                        'This member is already assigned to the project.'
                    ]
                ]);
            }

            $member=$project->members()->create([
                ...$data,
                'status'=>$data['status']??'active'
            ]);

            $this->forgetCaches();

            return $member;
        });
    }

    public function updateMember(
        ProjectMember $projectMember,
        array $data
    ): ProjectMember{
        return DB::transaction(function()use($projectMember,$data){
            $projectMember->update($data);

            $this->forgetCaches();

            return $projectMember->fresh();
        });
    }

    public function removeMember(ProjectMember $projectMember): void
    {
        DB::transaction(function()use($projectMember){
            $projectMember->delete();
            $this->forgetCaches();
        });
    }

    public function statistics(): array
    {
        return Cache::remember(
            'projects:statistics',
            now()->addMinutes(5),
            function(){
                $row=Project::query()
                    ->selectRaw("
                        COUNT(*) total,
                        COALESCE(SUM(budget),0) total_budget,
                        COALESCE(SUM(actual_cost),0) actual_cost,
                        COALESCE(AVG(progress),0) average_progress,
                        SUM(CASE WHEN status='planned' THEN 1 ELSE 0 END) planned,
                        SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) active,
                        SUM(CASE WHEN status='on_hold' THEN 1 ELSE 0 END) on_hold,
                        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed,
                        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled
                    ")
                    ->first();

                return [
                    'total'=>(int)($row->total??0),
                    'total_budget'=>(float)($row->total_budget??0),
                    'actual_cost'=>(float)($row->actual_cost??0),
                    'remaining_budget'=>
                        (float)($row->total_budget??0)
                        -(float)($row->actual_cost??0),
                    'average_progress'=>
                        round((float)($row->average_progress??0),2),
                    'planned'=>(int)($row->planned??0),
                    'active'=>(int)($row->active??0),
                    'on_hold'=>(int)($row->on_hold??0),
                    'completed'=>(int)($row->completed??0),
                    'cancelled'=>(int)($row->cancelled??0)
                ];
            }
        );
    }

    protected function generateProjectCode(): string
    {
        $prefix=strtoupper(
            trim((string)setting(
                'project_code_prefix',
                'PROJ'
            ))
        );

        if($prefix===''){
            $prefix='PROJ';
        }

        return $this->numberSequenceService->next(
            'project_code',
            $prefix.'-',
            6,
            fn()=>(int)Project::query()->max('id')
        );
    }

    protected function validateProjectDates(
        ?Project $project,
        array $data
    ): void{
        $start=$data['start_date']
            ??$project?->start_date?->toDateString();

        $expected=$data['expected_end_date']
            ??$project?->expected_end_date?->toDateString();

        $actual=$data['actual_end_date']
            ??$project?->actual_end_date?->toDateString();

        if($start&&$expected&&$expected<$start){
            throw ValidationException::withMessages([
                'expected_end_date'=>[
                    'Expected end date cannot be earlier than the project start date.'
                ]
            ]);
        }

        if($start&&$actual&&$actual<$start){
            throw ValidationException::withMessages([
                'actual_end_date'=>[
                    'Actual end date cannot be earlier than the project start date.'
                ]
            ]);
        }
    }

    public function forgetCaches(): void
    {
        Cache::forget('projects:statistics');
        Cache::forget('dashboard.projects.summary');

        $this->dashboardService->forgetDashboardCaches();
    }
}