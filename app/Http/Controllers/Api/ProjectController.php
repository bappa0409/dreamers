<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:planned,active,on_hold,completed,cancelled',
            'from'=>'nullable|date',
            'to'=>'nullable|date|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=Project::query()
            ->withCount('members')
            ->withSum('members as total_contribution','contribution')
            ->latest('id');

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('project_code','like',"%{$search}%")
                    ->orWhere('name','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhere('location','like',"%{$search}%");
            });
        }

        if(!empty($validated['status'])){
            $query->where(
                'status',
                $validated['status']
            );
        }

        if(!empty($validated['from'])){
            $query->whereDate(
                'start_date',
                '>=',
                $validated['from']
            );
        }

        if(!empty($validated['to'])){
            $query->whereDate(
                'start_date',
                '<=',
                $validated['to']
            );
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                min(
                    (int)($validated['per_page']??15),
                    100
                )
            )
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->projectService->statistics()
        ]);
    }

    public function members(Request $request)
    {
        $search=trim(
            (string)$request->input('search','')
        );

        $members=Member::query()
            ->with('user:id,name,email,mobile')
            ->where('status','active')
            ->when($search,function($query)use($search){
                $query->where(function($q)use($search){
                    $q->where(
                        'member_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'user',
                        function($uq)use($search){
                            $uq->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'mobile',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
                });
            })
            ->orderBy('member_code')
            ->limit(30)
            ->get();

        return response()->json([
            'success'=>true,
            'data'=>$members
        ]);
    }

    public function store(Request $request)
    {
        $validated=$this->validateProject($request);

        $project=$this->projectService->create(
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Project created successfully.',
            'data'=>$project
        ],201);
    }

    public function show(Project $project)
    {
        return response()->json([
            'success'=>true,
            'data'=>$project->load([
                'members.member.user'
            ])->loadSum(
                'members as total_contribution',
                'contribution'
            )
        ]);
    }

    public function update(
        Request $request,
        Project $project
    ){
        $validated=$this->validateProject(
            $request,
            true
        );

        $project=$this->projectService->update(
            $project,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Project updated successfully.',
            'data'=>$project
        ]);
    }

    public function storeMember(
        Request $request,
        Project $project
    ){
        $validated=$request->validate([
            'member_id'=>'required|exists:members,id',
            'contribution'=>'nullable|numeric|min:0',
            'role'=>'nullable|string|max:100',
            'joined_date'=>'nullable|date',
            'status'=>'nullable|in:active,inactive'
        ]);

        $member=$this->projectService->addMember(
            $project,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Member assigned successfully.',
            'data'=>$member->load('member.user')
        ],201);
    }

    public function updateMember(
        Request $request,
        Project $project,
        ProjectMember $projectMember
    ){
        abort_unless(
            $projectMember->project_id===$project->id,
            404
        );

        $validated=$request->validate([
            'contribution'=>'sometimes|nullable|numeric|min:0',
            'role'=>'sometimes|nullable|string|max:100',
            'joined_date'=>'sometimes|nullable|date',
            'status'=>'sometimes|required|in:active,inactive'
        ]);

        $member=$this->projectService->updateMember(
            $projectMember,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Project member updated successfully.',
            'data'=>$member->load('member.user')
        ]);
    }

    public function destroyMember(
        Project $project,
        ProjectMember $projectMember
    ){
        abort_unless(
            $projectMember->project_id===$project->id,
            404
        );

        $this->projectService->removeMember(
            $projectMember
        );

        return response()->json([
            'success'=>true,
            'message'=>'Project member removed successfully.'
        ]);
    }

    public function destroy(Project $project)
    {
        $this->projectService->delete($project);

        return response()->json([
            'success'=>true,
            'message'=>'Project deleted successfully.'
        ]);
    }

    protected function validateProject(
        Request $request,
        bool $update=false
    ): array{
        $required=$update
            ?'sometimes|required'
            :'required';

        return $request->validate([
            'name'=>"{$required}|string|max:255",
            'description'=>'nullable|string|max:5000',
            'location'=>'nullable|string|max:255',
            'budget'=>'nullable|numeric|min:0',
            'actual_cost'=>'nullable|numeric|min:0',
            'start_date'=>'nullable|date',
            'expected_end_date'=>'nullable|date|after_or_equal:start_date',
            'actual_end_date'=>'nullable|date',
            'progress'=>'nullable|integer|min:0|max:100',
            'status'=>'nullable|in:planned,active,on_hold,completed,cancelled',
            'notes'=>'nullable|string|max:5000'
        ]);
    }
}