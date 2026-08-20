<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class ApprovalWorkflowController extends Controller
{
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
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'module'=>'required|string|max:100',
            'action'=>'required|string|max:50',
            'is_active'=>'nullable|boolean',
            'approvers'=>'required|array|min:1|max:3',
            'approvers.*'=>'required|integer|distinct|exists:users,id',
        ]);

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
            'module'=>'required|string|max:100',
            'action'=>'required|string|max:50',
            'is_active'=>'required|boolean',
            'approvers'=>'required|array|min:1|max:3',
            'approvers.*'=>'required|integer|distinct|exists:users,id',
        ]);

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
    $users=User::query()
        ->where('is_active',true)
        ->select([
            'id',
            'name',
            'email',
        ])
        ->orderBy('name')
        ->get();

    return response()->json([
        'success'=>true,
        'data'=>$users,
    ]);
}
}