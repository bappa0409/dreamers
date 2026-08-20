<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Member;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    public function __construct(
        protected PasswordSetupService $passwordSetupService,
        protected DashboardService $dashboardService,
        protected MemberService $memberService
    ){}

    public function createRequest(
        Model $approvable,
        string $module,
        string $action,
        ?int $requestedBy=null,
        ?string $requestNote=null
    ): ApprovalRequest{
        return DB::transaction(function()use(
            $approvable,
            $module,
            $action,
            $requestedBy,
            $requestNote
        ){
            $existing=ApprovalRequest::query()
                ->where('approvable_type',$approvable->getMorphClass())
                ->where('approvable_id',$approvable->getKey())
                ->where('module',$module)
                ->where('action',$action)
                ->where('status','pending')
                ->first();

            if($existing){
                return $existing->load([
                    'steps.approver',
                    'approvable'
                ]);
            }

            $workflow=ApprovalWorkflow::query()
                ->where('module',$module)
                ->where('action',$action)
                ->where('is_active',true)
                ->with([
                    'steps'=>fn($query)=>
                        $query->orderBy('step_no')
                ])
                ->first();

            if(!$workflow||$workflow->steps->isEmpty()){
                throw ValidationException::withMessages([
                    'approval'=>[
                        "No active approval workflow is configured for {$module} {$action}."
                    ]
                ]);
            }

            $totalSteps=$workflow->steps->count();

            $approval=ApprovalRequest::create([
                'approvable_type'=>$approvable->getMorphClass(),
                'approvable_id'=>$approvable->getKey(),
                'module'=>$module,
                'action'=>$action,
                'status'=>'pending',
                'requested_by'=>$requestedBy,
                'request_note'=>$requestNote,
                'current_step'=>1,
                'total_steps'=>$totalSteps,
                'approved_by'=>null,
                'approved_at'=>null,
                'rejected_by'=>null,
                'rejected_at'=>null,
                'rejection_reason'=>null,
                'cancelled_by'=>null,
                'cancelled_at'=>null,
                'cancellation_reason'=>null,
                'completed_at'=>null,
            ]);

            $approval->steps()->createMany(
                $workflow->steps
                    ->map(fn($step)=>[
                        'step_no'=>$step->step_no,
                        'approver_user_id'=>$step->approver_user_id,
                        'status'=>'pending',
                        'remarks'=>null,
                        'approved_at'=>null,
                        'rejected_at'=>null,
                    ])
                    ->values()
                    ->all()
            );

            $this->forgetApprovalCaches();

            return $approval->load([
                'steps.approver',
                'approvable'
            ]);
        });
    }

    public function approve(
        ApprovalRequest $approvalRequest,
        int $userId,
        ?string $remarks=null
    ): ApprovalRequest{
        return DB::transaction(function()use(
            $approvalRequest,
            $userId,
            $remarks
        ){
            $approvalRequest=ApprovalRequest::query()
                ->with([
                    'steps',
                    'approvable'
                ])
                ->lockForUpdate()
                ->findOrFail($approvalRequest->id);

            if($approvalRequest->status!=='pending'){
                throw ValidationException::withMessages([
                    'approval'=>[
                        'Only pending approval requests can be approved.'
                    ]
                ]);
            }

            $currentStep=$approvalRequest->steps()
                ->where(
                    'step_no',
                    $approvalRequest->current_step
                )
                ->lockForUpdate()
                ->first();

            if(!$currentStep){
                throw ValidationException::withMessages([
                    'approval'=>[
                        'Current approval step was not found.'
                    ]
                ]);
            }

            if(
                (int)$currentStep->approver_user_id!==
                (int)$userId
            ){
                throw ValidationException::withMessages([
                    'approval'=>[
                        'You are not the current approver for this request.'
                    ]
                ]);
            }

            if($currentStep->status!=='pending'){
                throw ValidationException::withMessages([
                    'approval'=>[
                        'This approval step has already been processed.'
                    ]
                ]);
            }

            $currentStep->update([
                'status'=>'approved',
                'remarks'=>$remarks,
                'approved_at'=>now(),
                'rejected_at'=>null,
            ]);

            $isFinalStep=
                (int)$approvalRequest->current_step>=
                (int)$approvalRequest->total_steps;

            if(!$isFinalStep){
                $approvalRequest->update([
                    'current_step'=>
                        (int)$approvalRequest->current_step+1
                ]);

                $this->forgetApprovalCaches();

                return $approvalRequest->fresh([
                    'requester:id,name,email',
                    'approver:id,name,email',
                    'rejecter:id,name,email',
                    'steps.approver:id,name,email',
                    'approvable'
                ]);
            }

            $approvalRequest->update([
                'status'=>'approved',
                'approved_by'=>$userId,
                'approved_at'=>now(),
                'completed_at'=>now(),
            ]);

            $this->executeApprovedAction(
                $approvalRequest,
                $userId
            );

            $this->forgetApprovalCaches();

            return $approvalRequest->fresh([
                'requester:id,name,email',
                'approver:id,name,email',
                'rejecter:id,name,email',
                'steps.approver:id,name,email',
                'approvable'
            ]);
        });
    }

    public function reject(
        ApprovalRequest $approvalRequest,
        int $userId,
        string $reason,
        ?string $remarks=null
    ): ApprovalRequest{
        return DB::transaction(function()use(
            $approvalRequest,
            $userId,
            $reason,
            $remarks
        ){
            $approvalRequest=ApprovalRequest::query()
                ->with([
                    'steps',
                    'approvable'
                ])
                ->lockForUpdate()
                ->findOrFail($approvalRequest->id);

            if($approvalRequest->status!=='pending'){
                throw ValidationException::withMessages([
                    'approval'=>[
                        'Only pending approval requests can be rejected.'
                    ]
                ]);
            }

            $currentStep=$approvalRequest->steps()
                ->where(
                    'step_no',
                    $approvalRequest->current_step
                )
                ->lockForUpdate()
                ->first();

            if(!$currentStep){
                throw ValidationException::withMessages([
                    'approval'=>[
                        'Current approval step was not found.'
                    ]
                ]);
            }

            if(
                (int)$currentStep->approver_user_id!==
                (int)$userId
            ){
                throw ValidationException::withMessages([
                    'approval'=>[
                        'You are not the current approver for this request.'
                    ]
                ]);
            }

            $currentStep->update([
                'status'=>'rejected',
                'remarks'=>$remarks,
                'rejected_at'=>now(),
            ]);

            $approvalRequest->update([
                'status'=>'rejected',
                'rejected_by'=>$userId,
                'rejected_at'=>now(),
                'rejection_reason'=>$reason,
                'completed_at'=>now(),
            ]);

            $this->executeRejectedAction(
                $approvalRequest
            );

            $this->forgetApprovalCaches();

            return $approvalRequest->fresh([
                'requester:id,name,email',
                'approver:id,name,email',
                'rejecter:id,name,email',
                'steps.approver:id,name,email',
                'approvable'
            ]);
        });
    }

    public function cancel(
        ApprovalRequest $approvalRequest,
        int $userId,
        ?string $reason=null
    ): ApprovalRequest{
        return DB::transaction(function()use(
            $approvalRequest,
            $userId,
            $reason
        ){
            $approvalRequest=ApprovalRequest::query()
                ->with('approvable')
                ->lockForUpdate()
                ->findOrFail($approvalRequest->id);

            if($approvalRequest->status!=='pending'){
                throw ValidationException::withMessages([
                    'approval'=>[
                        'Only pending approval requests can be cancelled.'
                    ]
                ]);
            }

            $approvalRequest->update([
                'status'=>'cancelled',
                'cancelled_by'=>$userId,
                'cancelled_at'=>now(),
                'cancellation_reason'=>$reason,
                'completed_at'=>now(),
            ]);

            $this->executeCancelledAction(
                $approvalRequest
            );

            $this->forgetApprovalCaches();

            return $approvalRequest->fresh([
                'requester:id,name,email',
                'approver:id,name,email',
                'rejecter:id,name,email',
                'canceller:id,name,email',
                'steps.approver:id,name,email',
                'approvable'
            ]);
        });
    }

    protected function executeApprovedAction(
        ApprovalRequest $approvalRequest,
        int $approvedBy
    ): void{
        $approvable=$approvalRequest->approvable;

        if(!$approvable){
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Member Creation Approval
        |--------------------------------------------------------------------------
        */
        if(
            $approvable instanceof Member&&
            $approvalRequest->module==='Member'&&
            $approvalRequest->action==='create'
        ){
            $member=$this->memberService->approveMember(
                $approvable,
                $approvedBy
            );

            if($member->user){
                $this->passwordSetupService
                    ->sendSetupLink(
                        $member->user
                    );
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Future Approval Actions
        |--------------------------------------------------------------------------
        |
        | Project
        | Investment
        | Land
        | Notice
        | Expense
        |
        | তাদের business action পরে এখানে যোগ করা যাবে।
        |
        */
    }

    protected function executeRejectedAction(
        ApprovalRequest $approvalRequest
    ): void{
        $approvable=$approvalRequest->approvable;

        if(!$approvable){
            return;
        }

        if(
            $approvable instanceof Member&&
            $approvalRequest->module==='Member'&&
            $approvalRequest->action==='create'
        ){
            $approvable->update([
                'status'=>'rejected'
            ]);

            if($approvable->user){
                $approvable->user->update([
                    'is_active'=>false
                ]);

                $approvable->user
                    ->forgetAuthorizationCache();
            }

            $approvable->shares()
                ->where('status','pending')
                ->update([
                    'status'=>'cancelled'
                ]);

            $this->memberService
                ->forgetMemberCaches();
        }
    }

    protected function executeCancelledAction(
        ApprovalRequest $approvalRequest
    ): void{
        $approvable=$approvalRequest->approvable;

        if(!$approvable){
            return;
        }

        if(
            $approvable instanceof Member&&
            $approvalRequest->module==='Member'&&
            $approvalRequest->action==='create'
        ){
            if($approvable->status==='pending'){
                $approvable->update([
                    'status'=>'inactive'
                ]);
            }

            if($approvable->user){
                $approvable->user->update([
                    'is_active'=>false
                ]);

                $approvable->user
                    ->forgetAuthorizationCache();
            }

            $approvable->shares()
                ->where('status','pending')
                ->update([
                    'status'=>'cancelled'
                ]);

            $this->memberService
                ->forgetMemberCaches();
        }
    }

    public function forgetApprovalCaches(): void
    {
        Cache::forget('approvals:statistics');
        Cache::forget('dashboard.approvals.summary');
        Cache::forget('dashboard.approvals.pending');

        $this->dashboardService
            ->forgetDashboardCaches();
    }
}