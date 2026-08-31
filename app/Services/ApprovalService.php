<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Loan;
use App\Models\Member;
use App\Models\MemberExit;
use App\Models\MemberShare;
use App\Models\Tour;
use App\Models\User;
use App\Models\WelfareRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Account;

class ApprovalService
{
    public function __construct(
        protected PasswordSetupService $passwordSetupService,
        protected DashboardService $dashboardService,
        protected MemberService $memberService,
        protected LoanService $loanService,
        protected WelfareService $welfareService,
        protected MemberExitService $memberExitService,
        protected MemberShareService $memberShareService,
        protected TourService $tourService,
        protected AccountService $accountService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Look up the single pending approval request for a given approvable
     * model/module/action. Module controllers use this so their existing
     * approve/reject endpoints can keep working while delegating the actual
     * decision to this generic engine.
     */
    public function findPendingRequestFor(
        Model $approvable,
        string $module,
        string $action
    ): ApprovalRequest {
        $approvalRequest = ApprovalRequest::query()
            ->where('approvable_type', $approvable->getMorphClass())
            ->where('approvable_id', $approvable->getKey())
            ->where('module', $module)
            ->where('action', $action)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if (!$approvalRequest) {
            throw ValidationException::withMessages([
                'approval' => [
                    "No pending approval request was found for this {$module} {$action}."
                ]
            ]);
        }

        return $approvalRequest;
    }

    public function createRequest(
        Model $approvable,
        string $module,
        string $action,
        ?int $requestedBy = null,
        ?string $requestNote = null,
        array $decisionData = []
    ): ApprovalRequest {
        return DB::transaction(function () use (
            $approvable,
            $module,
            $action,
            $requestedBy,
            $requestNote,
            $decisionData
        ) {
            $existing = ApprovalRequest::query()
                ->where('approvable_type', $approvable->getMorphClass())
                ->where('approvable_id', $approvable->getKey())
                ->where('module', $module)
                ->where('action', $action)
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                return $existing->load(['steps.approver', 'approvable']);
            }

            $workflow = ApprovalWorkflow::query()
                ->where('module', $module)
                ->where('action', $action)
                ->where('is_active', true)
                ->with(['steps' => fn($query) => $query->orderBy('step_no')])
                ->first();

            if (!$workflow || $workflow->steps->isEmpty()) {
                throw ValidationException::withMessages([
                    'approval' => ["No active approval workflow is configured for {$module} {$action}."]
                ]);
            }

            $totalSteps = $workflow->steps->count();

            $approval = ApprovalRequest::create([
                'approvable_type' => $approvable->getMorphClass(),
                'approvable_id' => $approvable->getKey(),
                'module' => $module,
                'action' => $action,
                'status' => 'pending',
                'requested_by' => $requestedBy,
                'request_note' => $requestNote,
                'decision_data' => $decisionData ?: null, // <-- NEW
                'current_step' => 1,
                'total_steps' => $totalSteps,
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
                'cancelled_by' => null,
                'cancelled_at' => null,
                'cancellation_reason' => null,
                'completed_at' => null,
            ]);

            $approval->steps()->createMany(
                $workflow->steps->map(fn($step) => [
                    'step_no' => $step->step_no,
                    'approver_user_id' => $step->approver_user_id,
                    'status' => 'pending',
                    'remarks' => null,
                    'acted_at' => null,
                ])->values()->all()
            );

            $this->forgetApprovalCaches();

            $firstStep = $approval->steps->firstWhere('step_no', 1);
            $this->notifyStepApprover($approval, $firstStep?->approver_user_id, $requestedBy);

            return $approval->load(['steps.approver', 'approvable']);
        });
    }

    public function approve(
        ApprovalRequest $approvalRequest,
        int $userId,
        ?string $remarks = null,
        array $decisionData = []
    ): ApprovalRequest {
        return DB::transaction(function () use (
            $approvalRequest,
            $userId,
            $remarks,
            $decisionData
        ) {
            $approvalRequest = ApprovalRequest::query()
                ->with([
                    'steps',
                    'approvable'
                ])
                ->lockForUpdate()
                ->findOrFail($approvalRequest->id);

            if ($approvalRequest->status !== 'pending') {
                throw ValidationException::withMessages([
                    'approval' => [
                        'Only pending approval requests can be approved.'
                    ]
                ]);
            }

            $currentStep = $approvalRequest->steps()
                ->where(
                    'step_no',
                    $approvalRequest->current_step
                )
                ->lockForUpdate()
                ->first();

            if (!$currentStep) {
                throw ValidationException::withMessages([
                    'approval' => [
                        'Current approval step was not found.'
                    ]
                ]);
            }

            if (
                (int)$currentStep->approver_user_id !==
                (int)$userId
            ) {
                throw ValidationException::withMessages([
                    'approval' => [
                        'You are not the current approver for this request.'
                    ]
                ]);
            }

            if ($currentStep->status !== 'pending') {
                throw ValidationException::withMessages([
                    'approval' => [
                        'This approval step has already been processed.'
                    ]
                ]);
            }

            $currentStep->update([
                'status' => 'approved',
                'remarks' => $remarks,
                'acted_at' => now(),
            ]);

            if (!empty($decisionData)) {
                $approvalRequest->update([
                    'decision_data' => array_merge(
                        $approvalRequest->decision_data ?? [],
                        $decisionData
                    )
                ]);
            }

            $isFinalStep =
                (int)$approvalRequest->current_step >=
                (int)$approvalRequest->total_steps;

            if (!$isFinalStep) {
                $nextStepNo =
                    (int)$approvalRequest->current_step + 1;

                $approvalRequest->update([
                    'current_step' => $nextStepNo
                ]);

                $nextStep = $approvalRequest->steps
                    ->firstWhere('step_no', $nextStepNo);

                $this->notifyStepApprover(
                    $approvalRequest,
                    $nextStep?->approver_user_id,
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
            }

            $approvalRequest->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
                'completed_at' => now(),
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
        ?string $remarks = null
    ): ApprovalRequest {
        return DB::transaction(function () use (
            $approvalRequest,
            $userId,
            $reason,
            $remarks
        ) {
            $approvalRequest = ApprovalRequest::query()
                ->with([
                    'steps',
                    'approvable'
                ])
                ->lockForUpdate()
                ->findOrFail($approvalRequest->id);

            if ($approvalRequest->status !== 'pending') {
                throw ValidationException::withMessages([
                    'approval' => [
                        'Only pending approval requests can be rejected.'
                    ]
                ]);
            }

            $currentStep = $approvalRequest->steps()
                ->where(
                    'step_no',
                    $approvalRequest->current_step
                )
                ->lockForUpdate()
                ->first();

            if (!$currentStep) {
                throw ValidationException::withMessages([
                    'approval' => [
                        'Current approval step was not found.'
                    ]
                ]);
            }

            if (
                (int)$currentStep->approver_user_id !==
                (int)$userId
            ) {
                throw ValidationException::withMessages([
                    'approval' => [
                        'You are not the current approver for this request.'
                    ]
                ]);
            }

            $currentStep->update([
                'status' => 'rejected',
                'remarks' => $remarks,
                'acted_at' => now(),
            ]);

            $approvalRequest->update([
                'status' => 'rejected',
                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'completed_at' => now(),
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
        ?string $reason = null
    ): ApprovalRequest {
        return DB::transaction(function () use (
            $approvalRequest,
            $userId,
            $reason
        ) {
            $approvalRequest = ApprovalRequest::query()
                ->with('approvable')
                ->lockForUpdate()
                ->findOrFail($approvalRequest->id);

            if ($approvalRequest->status !== 'pending') {
                throw ValidationException::withMessages([
                    'approval' => [
                        'Only pending approval requests can be cancelled.'
                    ]
                ]);
            }

            $approvalRequest->update([
                'status' => 'cancelled',
                'cancelled_by' => $userId,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'completed_at' => now(),
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
    ): void {
        $approvable = $approvalRequest->approvable;

        if (!$approvable) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Member Creation Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof Member &&
            $approvalRequest->module === 'Member' &&
            $approvalRequest->action === 'create'
        ) {
            $member = $this->memberService->approveMember(
                $approvable,
                $approvedBy
            );

            if ($member->user) {
                $userId = (int)$member->user->id;

                DB::afterCommit(function () use ($userId) {
                    try {
                        $user = User::query()->find($userId);

                        if ($user && $user->email) {
                            $this->passwordSetupService->send($user);
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                });
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Loan Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof Loan &&
            $approvalRequest->module === 'Loan' &&
            $approvalRequest->action === 'approve'
        ) {
            $this->loanService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Welfare Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof WelfareRequest &&
            $approvalRequest->module === 'Welfare' &&
            $approvalRequest->action === 'approve'
        ) {
            $this->welfareService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Member Exit Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof MemberExit &&
            $approvalRequest->module === 'MemberExit' &&
            $approvalRequest->action === 'approve'
        ) {
            $this->memberExitService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Member Share Verification
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof MemberShare &&
            $approvalRequest->module === 'MemberShare' &&
            $approvalRequest->action === 'verify'
        ) {
            $this->memberShareService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Tour Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof Tour &&
            $approvalRequest->module === 'Tour' &&
            $approvalRequest->action === 'approve'
        ) {
            $this->tourService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Account Creation Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof Account &&
            $approvalRequest->module === 'Account' &&
            $approvalRequest->action === 'create'
        ) {
            $this->accountService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
|--------------------------------------------------------------------------
| Account Update Approval
|--------------------------------------------------------------------------
*/
        if (
            $approvable instanceof Account &&
            $approvalRequest->module === 'Account' &&
            $approvalRequest->action === 'update'
        ) {
            $this->accountService->update(
                $approvable,
                $approvalRequest->decision_data ?? []
            );

            return;
        }

        /*
|--------------------------------------------------------------------------
| Account Delete Approval
|--------------------------------------------------------------------------
*/
        if (
            $approvable instanceof Account &&
            $approvalRequest->module === 'Account' &&
            $approvalRequest->action === 'delete'
        ) {
            $this->accountService->delete(
                $approvable
            );

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
    ): void {
        $approvable = $approvalRequest->approvable;

        if (!$approvable) {
            return;
        }

        if (
            $approvable instanceof Member &&
            $approvalRequest->module === 'Member' &&
            $approvalRequest->action === 'create'
        ) {
            $approvable->update([
                'status' => 'rejected'
            ]);

            if ($approvable->user) {
                $approvable->user->update([
                    'is_active' => false
                ]);

                $approvable->user
                    ->forgetAuthorizationCache();
            }

            $approvable->shares()
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled'
                ]);

            $this->memberService
                ->forgetMemberCaches();

            return;
        }

        if (
            $approvable instanceof Loan &&
            $approvalRequest->module === 'Loan' &&
            $approvalRequest->action === 'approve'
        ) {
            $this->loanService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        if (
            $approvable instanceof WelfareRequest &&
            $approvalRequest->module === 'Welfare' &&
            $approvalRequest->action === 'approve'
        ) {
            $this->welfareService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        if (
            $approvable instanceof MemberExit &&
            $approvalRequest->module === 'MemberExit' &&
            $approvalRequest->action === 'approve'
        ) {
            $this->memberExitService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        if (
            $approvable instanceof MemberShare &&
            $approvalRequest->module === 'MemberShare' &&
            $approvalRequest->action === 'verify'
        ) {
            $this->memberShareService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        if (
            $approvable instanceof Tour &&
            $approvalRequest->module === 'Tour' &&
            $approvalRequest->action === 'approve'
        ) {
            $this->tourService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );
        }
        if (
            $approvable instanceof Account &&
            $approvalRequest->module === 'Account' &&
            $approvalRequest->action === 'create'
        ) {
            $this->accountService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }
    }

    protected function executeCancelledAction(
        ApprovalRequest $approvalRequest
    ): void {
        $approvable = $approvalRequest->approvable;

        if (!$approvable) {
            return;
        }

        if (
            $approvable instanceof Member &&
            $approvalRequest->module === 'Member' &&
            $approvalRequest->action === 'create'
        ) {
            if ($approvable->status === 'pending') {
                $approvable->update([
                    'status' => 'inactive'
                ]);
            }

            if ($approvable->user) {
                $approvable->user->update([
                    'is_active' => false
                ]);

                $approvable->user
                    ->forgetAuthorizationCache();
            }

            $approvable->shares()
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled'
                ]);

            $this->memberService
                ->forgetMemberCaches();
        }

        if (
            $approvable instanceof Account &&
            $approvalRequest->module === 'Account' &&
            $approvalRequest->action === 'create'
        ) {
            $this->accountService->finalizeCancellation($approvable);
        }
    }

    /**
     * Notify the approver whose turn it now is that an approval request
     * is waiting on them. Sent after the DB transaction commits so we
     * never notify for a step change that later gets rolled back.
     */
    protected function notifyStepApprover(
        ApprovalRequest $approvalRequest,
        ?int $approverUserId,
        ?int $senderId = null
    ): void {
        if (!$approverUserId) {
            return;
        }

        $approvalId = $approvalRequest->id;
        $module = $approvalRequest->module;
        $action = $approvalRequest->action;

        DB::afterCommit(function () use (
            $approvalId,
            $module,
            $action,
            $approverUserId,
            $senderId
        ) {
            try {
                $this->notificationService->sendSystem([
                    'title' => "Approval Pending: {$module}",
                    'message' =>
                    ucfirst($action) .
                        " request for {$module} (#{$approvalId}) is waiting for your approval.",
                    'type' => 'approval',
                    'audience_type' => 'users',
                    'user_ids' => [$approverUserId],
                    'action_url' => route('admin.approvals'),
                    'sent_by' => $senderId,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        });
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
