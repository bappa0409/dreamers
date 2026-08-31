<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class ApprovalWorkflowController extends Controller
{
    /**
     * Only module/action pairs that ApprovalService::executeApprovedAction()/
     * executeRejectedAction() actually check. Keeps the Workflow Builder from
     * accepting a free-text module/action that would silently do nothing —
     * add a new pair here only once the corresponding finalizeApproval()/
     * finalizeRejection() branch has been wired into ApprovalService.
     */
    public const WIRED_MODULE_ACTIONS = [
        'Member' => ['create'],
        'Loan' => ['approve'],
        'Welfare' => ['approve'],
        'MemberExit' => ['approve'],
        'MemberShare' => ['verify'],
        'Tour' => ['approve'],
        'Account' => ['create', 'update', 'delete'],
    ];

    public function index()
    {
        return response()->json([
            'success'=>true,
            'data'=>ApprovalWorkflow::query()
                ->with(
                    'steps.approver:id,name,email'
                )
                ->orderBy('module')
                ->orderBy('action')
                ->get(),
            'wired_module_actions'=>self::WIRED_MODULE_ACTIONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'module'=>[
                'required',
                'string',
                'max:100',
                Rule::in(array_keys(self::WIRED_MODULE_ACTIONS)),
            ],
            'action'=>'required|string|max:50',
            'is_active'=>'nullable|boolean',
            'approvers'=>'required|array|min:1|max:3',
            'approvers.*'=>'required|integer|distinct|exists:users,id',
        ]);

        $this->validateModuleAction(
            $validated['module'],
            $validated['action']
        );

        $this->validateApprovers($validated['approvers']);

        $exists=ApprovalWorkflow::query()
            ->where(
                'module',
                $validated['module']
            )
            ->where(
                'action',
                $validated['action']
            )
            ->exists();

        if($exists){
            throw ValidationException::withMessages([
                'workflow'=>[
                    'A workflow already exists for this module and action.'
                ],
            ]);
        }

        $workflow=DB::transaction(
            function()use($validated){
                $workflow=ApprovalWorkflow::create([
                    'module'=>$validated['module'],
                    'action'=>$validated['action'],
                    'is_active'=>$validated['is_active']??true,
                ]);

                foreach(
                    $validated['approvers']
                    as $index=>$userId
                ){
                    $workflow->steps()->create([
                        'step_no'=>$index+1,
                        'approver_user_id'=>$userId,
                    ]);
                }

                return $workflow;
            }
        );

        return response()->json([
            'success'=>true,
            'message'=>'Approval workflow created successfully.',
            'data'=>$workflow->load(
                'steps.approver:id,name,email'
            ),
        ],201);
    }

    public function update(
        Request $request,
        ApprovalWorkflow $approvalWorkflow
    ){
        $validated=$request->validate([
            'module'=>[
                'required',
                'string',
                'max:100',
                Rule::in(array_keys(self::WIRED_MODULE_ACTIONS)),
            ],
            'action'=>'required|string|max:50',
            'is_active'=>'required|boolean',
            'approvers'=>'required|array|min:1|max:3',
            'approvers.*'=>'required|integer|distinct|exists:users,id',
        ]);

        $this->validateModuleAction(
            $validated['module'],
            $validated['action']
        );

        $this->validateApprovers($validated['approvers']);

        $exists=ApprovalWorkflow::query()
            ->where(
                'module',
                $validated['module']
            )
            ->where(
                'action',
                $validated['action']
            )
            ->where(
                'id',
                '!=',
                $approvalWorkflow->id
            )
            ->exists();

        if($exists){
            throw ValidationException::withMessages([
                'workflow'=>[
                    'A workflow already exists for this module and action.'
                ],
            ]);
        }

        DB::transaction(
            function()use(
                $approvalWorkflow,
                $validated
            ){
                $approvalWorkflow->update([
                    'module'=>$validated['module'],
                    'action'=>$validated['action'],
                    'is_active'=>$validated['is_active'],
                ]);

                $approvalWorkflow
                    ->steps()
                    ->delete();

                foreach(
                    $validated['approvers']
                    as $index=>$userId
                ){
                    $approvalWorkflow
                        ->steps()
                        ->create([
                            'step_no'=>$index+1,
                            'approver_user_id'=>$userId,
                        ]);
                }
            }
        );

        return response()->json([
            'success'=>true,
            'message'=>'Approval workflow updated successfully.',
            'data'=>$approvalWorkflow
                ->fresh(
                    'steps.approver:id,name,email'
                ),
        ]);
    }

    public function destroy(
        ApprovalWorkflow $approvalWorkflow
    ){
        $approvalWorkflow->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Approval workflow deleted successfully.',
        ]);
    }

    public function toggle(
        ApprovalWorkflow $approvalWorkflow
    ){
        $approvalWorkflow->update([
            'is_active'=>
                !$approvalWorkflow->is_active,
        ]);

        return response()->json([
            'success'=>true,
            'message'=>$approvalWorkflow->is_active
                ?'Workflow activated successfully.'
                :'Workflow deactivated successfully.',
            'data'=>$approvalWorkflow->fresh(),
        ]);
    }
    public function approvers()
    {
        $users=$this->eligibleApproversQuery()
            ->select([
                'users.id',
                'users.name',
                'users.email',
            ])
            ->orderBy('users.name')
            ->get();

        return response()->json([
            'success'=>true,
            'data'=>$users,
        ]);
    }

    protected function validateModuleAction(string $module,string $action): void
    {
        $allowedActions=self::WIRED_MODULE_ACTIONS[$module]??[];

        if(!in_array($action,$allowedActions,true)){
            throw ValidationException::withMessages([
                'action'=>[
                    "'{$action}' is not a valid action for module '{$module}'. Allowed: ".
                    (empty($allowedActions)
                        ?'none'
                        :implode(', ',$allowedActions)).'.'
                ],
            ]);
        }
    }

    protected function validateApprovers(array $userIds): void
    {
        $requested=collect($userIds)
            ->map(fn($id)=>(int)$id)
            ->unique()
            ->values();

        $eligible=$this->eligibleApproversQuery()
            ->whereIn('users.id',$requested->all())
            ->pluck('users.id')
            ->map(fn($id)=>(int)$id)
            ->unique();

        if($eligible->count()!==$requested->count()){
            throw ValidationException::withMessages([
                'approvers'=>[
                    'Every approver must be an active user with approval permission.'
                ],
            ]);
        }
    }

    protected function eligibleApproversQuery()
    {
        return User::query()
            ->where('users.is_active',true)
            ->where(function($query){
                $query
                    ->whereHas('roles',function($roleQuery){
                        $roleQuery->where('roles.name','system_analyst');
                    })
                    ->orWhereHas('roles.permissions',function($permissionQuery){
                        $permissionQuery->where(
                            'permissions.name',
                            'Approval.approve'
                        );
                    });
            });
    }
}