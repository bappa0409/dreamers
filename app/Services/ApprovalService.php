<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use App\Services\PasswordSetupService;
use Illuminate\Support\Facades\Log;
use App\Services\DashboardService;
use Illuminate\Database\Eloquent\Model;

class ApprovalService
{
    public function __construct(
        protected PasswordSetupService $passwordSetupService,
        protected DashboardService $dashboardService
    ) {}

    public function createRequest(
        Model $approvable,
        string $module,
        string $action,
        ?int $requestedBy = null,
        ?string $note = null
    ): ApprovalRequest {
        $approval = ApprovalRequest::create([
            'approvable_type' => $approvable::class,
            'approvable_id' => $approvable->getKey(),
            'module' => $module,
            'action' => $action,
            'status' => 'pending',
            'requested_by' => $requestedBy,
            'request_note' => $note,
        ]);

        $this->forgetApprovalCaches();

        return $approval;
    }

    public function approve(ApprovalRequest $approval, int $approvedBy): ApprovalRequest
    {
        return DB::transaction(function () use ($approval, $approvedBy) {
            $approval = ApprovalRequest::query()
                ->lockForUpdate()
                ->findOrFail($approval->id);

            if ($approval->status !== 'pending') {
                throw ValidationException::withMessages([
                    'approval' => ['This approval request has already been processed.']
                ]);
            }

            $subject = $approval->approvable;
            $setupUser = null;

            if ($subject instanceof Member) {
                $subject = Member::query()
                    ->with('user')
                    ->lockForUpdate()
                    ->findOrFail($subject->id);

                if (!$subject->user) {
                    throw ValidationException::withMessages([
                        'member' => ['The member does not have a linked user account.']
                    ]);
                }

                $subject->update([
                    'status' => 'active',
                    'joining_date' => $subject->joining_date ?? now()->toDateString(),
                ]);

                $subject->user->update([
                    'is_active' => true
                ]);

                $setupUser = $subject->user;
            }

            $approval->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
                'cancelled_by' => null,
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);

            $this->forgetApprovalCaches();
            $this->forgetMemberCaches();

            if ($setupUser) {
                DB::afterCommit(function () use ($setupUser) {
                    try {
                        $this->passwordSetupService->send($setupUser);
                    } catch (\Throwable $e) {
                        Log::error('Password setup email failed.', [
                            'user_id' => $setupUser->id,
                            'email' => $setupUser->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                });
            }

            return $approval->fresh([
                'approvable',
                'requester',
                'approver'
            ]);
        });
    }


    public function reject(
        ApprovalRequest $approval,
        int $rejectedBy,
        ?string $reason = null
    ): ApprovalRequest {
        return DB::transaction(function () use ($approval, $rejectedBy, $reason) {
            $approval = ApprovalRequest::query()
                ->lockForUpdate()
                ->findOrFail($approval->id);

            if ($approval->status !== 'pending') {
                throw ValidationException::withMessages([
                    'approval' => ['This approval request has already been processed.']
                ]);
            }

            $subject = $approval->approvable;

            if ($subject instanceof Member) {
                $subject = Member::query()
                    ->with('user')
                    ->lockForUpdate()
                    ->findOrFail($subject->id);

                $subject->update([
                    'status' => 'rejected'
                ]);

                if ($subject->user) {
                    $subject->user->update([
                        'is_active' => false,
                        'password_setup_token' => null,
                        'password_setup_expires_at' => null,
                    ]);
                }
            }

            $approval->update([
                'status' => 'rejected',
                'rejected_by' => $rejectedBy,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'approved_by' => null,
                'approved_at' => null,
                'cancelled_by' => null,
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);

            $this->forgetApprovalCaches();
            $this->forgetMemberCaches();

            return $approval->fresh([
                'approvable',
                'requester',
                'rejecter'
            ]);
        });
    }

    public function cancel(
        ApprovalRequest $approval,
        int $cancelledBy,
        ?string $reason = null
    ): ApprovalRequest {
        return DB::transaction(function () use ($approval, $cancelledBy, $reason) {
            $approval = ApprovalRequest::query()
                ->lockForUpdate()
                ->findOrFail($approval->id);

            if ($approval->status !== 'pending') {
                throw ValidationException::withMessages([
                    'approval' => ['Only pending approval requests can be cancelled.']
                ]);
            }

            $approval->update([
                'status' => 'cancelled',
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            $this->forgetApprovalCaches();

            return $approval->fresh([
                'approvable',
                'requester',
                'canceller'
            ]);
        });
    }

    public function forgetApprovalCaches(): void
    {
        Cache::forget('approvals:statistics');
        Cache::forget('dashboard.approvals.summary');
        Cache::forget('dashboard.approvals.pending');
        $this->dashboardService->forgetDashboardCaches();
    }

    protected function forgetMemberCaches(): void
    {
        Cache::forget('members:summary');
        Cache::forget('dashboard.members.summary');
        Cache::forget('dashboard.members.active_count');
        Cache::forget('dashboard.members.pending_count');
        $this->dashboardService->forgetDashboardCaches();
    }
}
