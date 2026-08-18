<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Investment;
use App\Models\Member;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getDashboardData(User $user): array
    {
        return [
            'members'=>$this->memberSummary($user),
            'approvals'=>$this->approvalSummary($user),
            'investments'=>$this->investmentSummary($user),
            'projects'=>$this->projectSummary($user),
            'recent_members'=>$this->recentMembers($user),
            'recent_approvals'=>$this->recentApprovals($user),
        ];
    }

    protected function memberSummary(User $user): ?array
    {
        if(!$user->hasPermission('Member.view'))return null;

        return Cache::remember('dashboard:members:summary',now()->addMinutes(10),function(){
            $row=Member::query()->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status='suspended' THEN 1 ELSE 0 END) AS suspended,
                SUM(CASE WHEN status IN ('inactive','rejected') THEN 1 ELSE 0 END) AS inactive
            ")->first();

            return [
                'total'=>(int)($row->total??0),
                'active'=>(int)($row->active??0),
                'pending'=>(int)($row->pending??0),
                'suspended'=>(int)($row->suspended??0),
                'inactive'=>(int)($row->inactive??0),
            ];
        });
    }

    protected function approvalSummary(User $user): ?array
    {
        if(!$user->hasPermission('Approval.view'))return null;

        return Cache::remember('dashboard:approvals:summary',now()->addMinutes(5),function(){
            $row=ApprovalRequest::query()->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) AS rejected,
                SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled
            ")->first();

            return [
                'total'=>(int)($row->total??0),
                'pending'=>(int)($row->pending??0),
                'approved'=>(int)($row->approved??0),
                'rejected'=>(int)($row->rejected??0),
                'cancelled'=>(int)($row->cancelled??0),
            ];
        });
    }

    protected function investmentSummary(User $user): ?array
    {
        if(!$user->hasPermission('Investment.view'))return null;

        return Cache::remember('dashboard:investments:summary',now()->addMinutes(10),function(){
            return [
                'total'=>Investment::query()->count(),
                'active'=>Investment::query()->where('status','active')->count(),
                'total_amount'=>(float)(Investment::query()->sum('amount')??0),
            ];
        });
    }

    protected function projectSummary(User $user): ?array
    {
        if(!$user->hasPermission('Project.view'))return null;

        return Cache::remember('dashboard:projects:summary',now()->addMinutes(10),function(){
            return [
                'total'=>Project::query()->count(),
                'active'=>Project::query()->where('status','active')->count(),
                'completed'=>Project::query()->where('status','completed')->count(),
            ];
        });
    }

    protected function recentMembers(User $user): array
    {
        if(!$user->hasPermission('Member.view'))return [];

        return Cache::remember('dashboard:members:recent',now()->addMinutes(5),function(){
            return Member::query()
                ->with('user:id,name,email')
                ->latest('id')
                ->limit(6)
                ->get([
                    'id','user_id','member_code','status','joining_date','created_at'
                ])
                ->toArray();
        });
    }

    protected function recentApprovals(User $user): array
    {
        if(!$user->hasPermission('Approval.view'))return [];

        return Cache::remember('dashboard:approvals:recent',now()->addMinutes(3),function(){
            return ApprovalRequest::query()
                ->with('requester:id,name')
                ->latest('id')
                ->limit(6)
                ->get([
                    'id','module','action','status','requested_by','created_at'
                ])
                ->toArray();
        });
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