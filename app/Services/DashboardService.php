<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Investment;
use App\Models\Member;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getDashboardData(User $user): array
    {
        return[
            'members'=>$this->memberSummary($user),
            'approvals'=>$this->approvalSummary($user),
            'investments'=>$this->investmentSummary($user),
            'projects'=>$this->projectSummary($user),
            'recent_members'=>$this->recentMembers($user),
            'recent_approvals'=>$this->recentApprovals($user),
            'no_approval_workflow'=>$this->noApprovalWorkflowConfigured($user),
        ];
    }

    /**
     * True when the admin has permission to manage approvals but no active,
     * fully-configured workflow exists yet — meaning every module/action
     * currently auto-approves instead of waiting on a reviewer
     * (see ApprovalService::createRequest()).
     */
    protected function noApprovalWorkflowConfigured(User $user): bool
    {
        if(!$user->hasPermission('Approval.view')){
            return false;
        }

        return !ApprovalWorkflow::query()
            ->where('is_active', true)
            ->whereHas('steps')
            ->exists();
    }

    protected function memberSummary(User $user): ?array
    {
        if(!$user->hasPermission('Member.view'))return null;

        return Cache::remember(
            'dashboard:members:summary',
            now()->addMinutes(10),
            function(){
                $row=Member::query()
                    ->selectRaw("
                        COUNT(*) total,
                        SUM(status='active') active,
                        SUM(status='pending') pending,
                        SUM(status='suspended') suspended,
                        SUM(status IN ('inactive','rejected')) inactive
                    ")
                    ->first();

                return[
                    'total'=>(int)($row->total??0),
                    'active'=>(int)($row->active??0),
                    'pending'=>(int)($row->pending??0),
                    'suspended'=>(int)($row->suspended??0),
                    'inactive'=>(int)($row->inactive??0),
                ];
            }
        );
    }

    protected function approvalSummary(User $user): ?array
    {
        if(!$user->hasPermission('Approval.view'))return null;

        return Cache::remember(
            'dashboard:approvals:summary',
            now()->addMinutes(5),
            function(){
                $row=ApprovalRequest::query()
                    ->selectRaw("
                        COUNT(*) total,
                        SUM(status='pending') pending,
                        SUM(status='approved') approved,
                        SUM(status='rejected') rejected,
                        SUM(status='cancelled') cancelled
                    ")
                    ->first();

                return[
                    'total'=>(int)($row->total??0),
                    'pending'=>(int)($row->pending??0),
                    'approved'=>(int)($row->approved??0),
                    'rejected'=>(int)($row->rejected??0),
                    'cancelled'=>(int)($row->cancelled??0),
                ];
            }
        );
    }

    protected function investmentSummary(User $user): ?array
    {
        if(!$user->hasPermission('Investment.view'))return null;

        return Cache::remember(
            'dashboard:investments:summary',
            now()->addMinutes(10),
            function(){
                $row=Investment::query()
                    ->selectRaw("
                        COUNT(*) total,
                        SUM(status='active') active,
                        COALESCE(SUM(amount),0) total_amount
                    ")
                    ->first();

                return[
                    'total'=>(int)($row->total??0),
                    'active'=>(int)($row->active??0),
                    'total_amount'=>(float)($row->total_amount??0),
                ];
            }
        );
    }

    protected function projectSummary(User $user): ?array
    {
        if(!$user->hasPermission('Project.view'))return null;

        return Cache::remember(
            'dashboard:projects:summary',
            now()->addMinutes(10),
            function(){
                $row=Project::query()
                    ->selectRaw("
                        COUNT(*) total,
                        SUM(status='active') active,
                        SUM(status='completed') completed
                    ")
                    ->first();

                return[
                    'total'=>(int)($row->total??0),
                    'active'=>(int)($row->active??0),
                    'completed'=>(int)($row->completed??0),
                ];
            }
        );
    }

    protected function recentMembers(User $user): array
    {
        if(!$user->hasPermission('Member.view'))return[];

        return Cache::remember(
            'dashboard:members:recent',
            now()->addMinutes(5),
            fn()=>Member::query()
                ->select([
                    'id',
                    'user_id',
                    'member_code',
                    'status',
                    'joining_date',
                    'created_at',
                ])
                ->with('user:id,name,email')
                ->latest('id')
                ->limit(6)
                ->get()
                ->toArray()
        );
    }

    protected function recentApprovals(User $user): array
    {
        if(!$user->hasPermission('Approval.view'))return[];

        return Cache::remember(
            'dashboard:approvals:recent',
            now()->addMinutes(3),
            fn()=>ApprovalRequest::query()
                ->select([
                    'id',
                    'module',
                    'action',
                    'status',
                    'requested_by',
                    'created_at',
                ])
                ->with('requester:id,name')
                ->latest('id')
                ->limit(6)
                ->get()
                ->toArray()
        );
    }

    public function forgetDashboardCaches(): void
    {
        foreach([
            'dashboard:members:summary',
            'dashboard:approvals:summary',
            'dashboard:investments:summary',
            'dashboard:projects:summary',
            'dashboard:members:recent',
            'dashboard:approvals:recent',
        ] as $key){
            Cache::forget($key);
        }
    }
}