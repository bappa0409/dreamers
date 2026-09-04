<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Asset;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Loan;
use App\Models\Member;
use App\Models\MemberCharge;
use App\Models\MemberExit;
use App\Models\MemberShare;
use App\Models\SubscriptionPayment;
use App\Models\Tour;
use App\Models\Transaction;
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
        protected IncomeService $incomeService,
        protected ExpenseService $expenseService,
        protected SubscriptionService $subscriptionService,
        protected NotificationService $notificationService,
        protected ChargeService $chargeService,
        protected AssetService $assetService,
        protected JournalService $journalService
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

            /*
            |--------------------------------------------------------------------------
            | Backward-compat action alias
            |--------------------------------------------------------------------------
            |
            | MemberShareController::store()/purchase() always create the
            | ApprovalRequest with action='request'. The Approval Workflow
            | Builder's dropdown for MemberShare used to only offer
            | 'verify' as the action (see ApprovalWorkflowController), so
            | any workflow an admin built through the UI before that was
            | fixed got saved as action='verify' — which never matches
            | 'request' here, so the lookup below found nothing and the
            | purchase auto-approved instead of waiting on the configured
            | approvers. Accepting the legacy 'verify' value here too means
            | an already-saved workflow keeps working immediately, with no
            | need to re-open and re-save it in the Workflow Builder.
            |
            | Same drift happened for MemberExit: MemberExitController
            | always creates/looks up the request with action='request',
            | but the Workflow Builder used to only offer 'approve' for
            | that module, so any workflow saved before that was fixed
            | has action='approve' on it.
            */
            $legacyActionAliases = [
                'MemberShare' => 'verify',
                'MemberExit' => 'approve',
                'Loan' => 'approve',
                'Welfare' => 'approve',
            ];

            $workflowActionMatches = $action === 'request' && isset($legacyActionAliases[$module])
                ? ['request', $legacyActionAliases[$module]]
                : [$action];

            $workflow = ApprovalWorkflow::query()
                ->where('module', $module)
                ->whereIn('action', $workflowActionMatches)
                ->where('is_active', true)
                ->with(['steps' => fn($query) => $query->orderBy('step_no')])
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Approval skipped (module/action unmarked)
            |--------------------------------------------------------------------------
            |
            | No active workflow configured for this module/action — either it was
            | never set up, or an admin deactivated/unmarked it from the Approval
            | Workflow Builder (resources/views/admin/approvals). In that case we
            | don't block the action: it's auto-approved immediately, running the
            | exact same posting logic a normal final approval would run. Any
            | module/action the admin keeps marked/active still goes through the
            | full approval flow below, unaffected.
            |
            */
            if (!$workflow || $workflow->steps->isEmpty()) {
                return $this->autoApprove(
                    $approvable,
                    $module,
                    $action,
                    $requestedBy,
                    $requestNote,
                    $decisionData
                );
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

    /**
     * Used when a module/action has no active approval workflow — i.e. an
     * admin deactivated ("unmarked") it in the Approval Workflow Builder,
     * or never configured one. Records the request as already approved
     * (self-approved by whoever triggered it) and immediately runs the
     * exact same posting logic the normal flow runs on final approval
     * (executeApprovedAction) — so ledger postings, statuses and audit
     * trail end up identical to a normal approval, just without anyone
     * having to wait or act on it.
     */
    protected function autoApprove(
        Model $approvable,
        string $module,
        string $action,
        ?int $requestedBy,
        ?string $requestNote,
        array $decisionData = []
    ): ApprovalRequest {
        $approval = ApprovalRequest::create([
            'approvable_type' => $approvable->getMorphClass(),
            'approvable_id' => $approvable->getKey(),
            'module' => $module,
            'action' => $action,
            'status' => 'approved',
            'requested_by' => $requestedBy,
            'request_note' => $requestNote,
            'decision_data' => $decisionData ?: null,
            'current_step' => 1,
            'total_steps' => 0,
            'approved_by' => $requestedBy,
            'approved_at' => now(),
            'completed_at' => now(),
        ]);

        $this->executeApprovedAction(
            $approval,
            (int) ($requestedBy ?? 0)
        );

        $this->forgetApprovalCaches();

        return $approval->load(['steps.approver', 'approvable']);
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
        |
        | NOTE: LoanController always creates/looks up this request with
        | action='request' (see store()/verify()/reject()) — must match
        | here too, not 'approve'.
        */
        if (
            $approvable instanceof Loan &&
            $approvalRequest->module === 'Loan' &&
            $approvalRequest->action === 'request'
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
        |
        | NOTE: WelfareController always creates/looks up this request
        | with action='request' — must match here too, not 'approve'.
        */
        if (
            $approvable instanceof WelfareRequest &&
            $approvalRequest->module === 'Welfare' &&
            $approvalRequest->action === 'request'
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
        |
        | NOTE: MemberExitController always creates/looks up this request
        | with action='request' (see store()/verify()/reject()), so this
        | must check 'request' too, not 'approve' — otherwise the request
        | reaches status=approved but finalizeApproval() (ledger postings,
        | member/share status updates) never actually runs.
        */
        if (
            $approvable instanceof MemberExit &&
            $approvalRequest->module === 'MemberExit' &&
            $approvalRequest->action === 'request'
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
        |
        | NOTE: action must match what MemberShareController::store()/purchase()
        | passes into ApprovalService::createRequest() — which is 'request', NOT
        | 'verify'. Previously this checked action === 'verify', so it never
        | matched, finalizeApproval() never ran, and the share stayed 'pending'
        | forever even after the ApprovalRequest itself was approved/auto-approved.
        |
        */
        if (
            $approvable instanceof MemberShare &&
            $approvalRequest->module === 'MemberShare' &&
            $approvalRequest->action === 'request'
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
        | Income Creation Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof Income &&
            $approvalRequest->module === 'Income' &&
            $approvalRequest->action === 'create'
        ) {
            $this->incomeService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Expense Creation Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof Expense &&
            $approvalRequest->module === 'Expense' &&
            $approvalRequest->action === 'create'
        ) {
            $this->expenseService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Subscription Payment Verification
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof SubscriptionPayment &&
            $approvalRequest->module === 'SubscriptionPayment' &&
            $approvalRequest->action === 'verify'
        ) {
            $this->subscriptionService->verifyPayment(
                $approvable,
                $approvedBy,
                $approvalRequest->decision_data['note'] ?? null
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Member Charge Creation Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof MemberCharge &&
            $approvalRequest->module === 'Charge' &&
            $approvalRequest->action === 'create'
        ) {
            $this->chargeService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Asset Creation Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof Asset &&
            $approvalRequest->module === 'Asset' &&
            $approvalRequest->action === 'create'
        ) {
            $this->assetService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Journal Entry (Manual) Creation Approval
        |--------------------------------------------------------------------------
        */
        if (
            $approvable instanceof Transaction &&
            $approvalRequest->module === 'JournalEntry' &&
            $approvalRequest->action === 'create'
        ) {
            $this->journalService->finalizeApproval(
                $approvable,
                $approvalRequest->decision_data ?? [],
                $approvedBy
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

        // NOTE: same fix as executeApprovedAction() above — must match
        // action='request', not 'approve'.
        if (
            $approvable instanceof Loan &&
            $approvalRequest->module === 'Loan' &&
            $approvalRequest->action === 'request'
        ) {
            $this->loanService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        // NOTE: same fix as executeApprovedAction() above — must match
        // action='request', not 'approve'.
        if (
            $approvable instanceof WelfareRequest &&
            $approvalRequest->module === 'Welfare' &&
            $approvalRequest->action === 'request'
        ) {
            $this->welfareService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        // NOTE: same fix as executeApprovedAction() above — action must be
        // 'request' to match how the ApprovalRequest was created, not
        // 'approve'.
        if (
            $approvable instanceof MemberExit &&
            $approvalRequest->module === 'MemberExit' &&
            $approvalRequest->action === 'request'
        ) {
            $this->memberExitService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Member Share Rejection
        |--------------------------------------------------------------------------
        |
        | NOTE: same fix as executeApprovedAction() above — action must be
        | 'request' to match how the ApprovalRequest was created, not 'verify'.
        |
        */
        if (
            $approvable instanceof MemberShare &&
            $approvalRequest->module === 'MemberShare' &&
            $approvalRequest->action === 'request'
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

            return;
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

        if (
            $approvable instanceof Income &&
            $approvalRequest->module === 'Income' &&
            $approvalRequest->action === 'create'
        ) {
            $this->incomeService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        if (
            $approvable instanceof Expense &&
            $approvalRequest->module === 'Expense' &&
            $approvalRequest->action === 'create'
        ) {
            $this->expenseService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        if (
            $approvable instanceof SubscriptionPayment &&
            $approvalRequest->module === 'SubscriptionPayment' &&
            $approvalRequest->action === 'verify'
        ) {
            $this->subscriptionService->rejectPayment(
                $approvable,
                (int) $approvalRequest->rejected_by,
                $approvalRequest->rejection_reason ?? 'Rejected.'
            );

            return;
        }

        if (
            $approvable instanceof MemberCharge &&
            $approvalRequest->module === 'Charge' &&
            $approvalRequest->action === 'create'
        ) {
            $this->chargeService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        if (
            $approvable instanceof Asset &&
            $approvalRequest->module === 'Asset' &&
            $approvalRequest->action === 'create'
        ) {
            $this->assetService->finalizeRejection(
                $approvable,
                $approvalRequest->rejection_reason ?? '',
                $approvalRequest->rejected_by
            );

            return;
        }

        if (
            $approvable instanceof Transaction &&
            $approvalRequest->module === 'JournalEntry' &&
            $approvalRequest->action === 'create'
        ) {
            $this->journalService->finalizeRejection(
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

        if (
            $approvable instanceof Income &&
            $approvalRequest->module === 'Income' &&
            $approvalRequest->action === 'create'
        ) {
            $this->incomeService->finalizeCancellation($approvable);
        }

        if (
            $approvable instanceof Expense &&
            $approvalRequest->module === 'Expense' &&
            $approvalRequest->action === 'create'
        ) {
            $this->expenseService->finalizeCancellation($approvable);
        }

        if (
            $approvable instanceof SubscriptionPayment &&
            $approvalRequest->module === 'SubscriptionPayment' &&
            $approvalRequest->action === 'verify' &&
            $approvable->status === 'pending'
        ) {
            $this->subscriptionService->rejectPayment(
                $approvable,
                (int) ($approvalRequest->cancelled_by ?? 0),
                $approvalRequest->cancellation_reason
                    ?? 'Verification request cancelled.'
            );
        }

        if (
            $approvable instanceof MemberCharge &&
            $approvalRequest->module === 'Charge' &&
            $approvalRequest->action === 'create'
        ) {
            $this->chargeService->finalizeCancellation($approvable);
        }

        if (
            $approvable instanceof Asset &&
            $approvalRequest->module === 'Asset' &&
            $approvalRequest->action === 'create'
        ) {
            $this->assetService->finalizeCancellation($approvable);
        }

        if (
            $approvable instanceof Transaction &&
            $approvalRequest->module === 'JournalEntry' &&
            $approvalRequest->action === 'create'
        ) {
            $this->journalService->finalizeCancellation($approvable);
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