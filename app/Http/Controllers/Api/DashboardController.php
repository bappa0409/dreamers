<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Member;
use App\Models\Account;
use App\Models\Investment;
use App\Models\Land;
use App\Models\Project;
use App\Models\Poll;
use App\Models\Notice;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'users' => $this->userStats(),

            'members' => $this->memberStats(),

            'finance' => $this->financeStats(),

            'investments' => $this->investmentStats(),
            'monthly_investments' => $this->monthlyInvestments(),

            'land' => $this->landStats(),

            'projects' => $this->projectStats(),

            'polls' => $this->pollStats(),

            'notices' => $this->noticeStats(),
            'monthly_members' => $this->monthlyMembers(),

            'recent_members' => Member::with('user')
                ->latest()
                ->limit(5)
                ->get(),

            'recent_activities' => $this->recentActivities(),
        ]);
    }

    private function userStats(): array
    {
        return [
            'total' => User::count(),
        ];
    }

    private function monthlyInvestments(): array
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {

            $data[] = [
                'month' => $month,

                'amount' => Investment::whereYear(
                    'investment_date',
                    now()->year
                )
                ->whereMonth(
                    'investment_date',
                    $month
                )
                ->sum('amount'),
            ];
        }

        return $data;
    }

    private function memberStats(): array
    {
        return [
            'total' => Member::count(),

            'active' => Member::where(
                'status',
                'active'
            )->count(),

            'pending' => Member::where(
                'status',
                'pending'
            )->count(),

            'inactive' => Member::where(
                'status',
                'inactive'
            )->count(),

            'suspended' => Member::where(
                'status',
                'suspended'
            )->count(),
        ];
    }

    private function financeStats(): array
    {
        return [
            'total_accounts' => Account::count(),

            'active_accounts' => Account::where(
                'is_active',
                true
            )->count(),
        ];
    }

    private function investmentStats(): array
    {
        return [
            'total' => Investment::count(),

            'active' => Investment::where(
                'status',
                'active'
            )->count(),

            'completed' => Investment::where(
                'status',
                'completed'
            )->count(),

            'total_amount' => Investment::sum('amount'),
        ];
    }

    private function landStats(): array
    {
        return [
            'total' => Land::count(),

            'planned' => Land::where(
                'status',
                'planned'
            )->count(),

            'purchased' => Land::where(
                'status',
                'purchased'
            )->count(),

            'sold' => Land::where(
                'status',
                'sold'
            )->count(),

            'total_purchase_price' => Land::sum(
                'purchase_price'
            ),
        ];
    }

    private function projectStats(): array
    {
        return [
            'total' => Project::count(),

            'active' => Project::where(
                'status',
                'active'
            )->count(),

            'completed' => Project::where(
                'status',
                'completed'
            )->count(),

            'on_hold' => Project::where(
                'status',
                'on_hold'
            )->count(),

            'total_budget' => Project::sum('budget'),

            'actual_cost' => Project::sum('actual_cost'),
        ];
    }

    private function pollStats(): array
    {
        return [
            'total' => Poll::count(),

            'active' => Poll::where(
                'is_active',
                true
            )->count(),
        ];
    }

    private function noticeStats(): array
    {
        return [
            'total' => Notice::count(),

            'published' => Notice::where(
                'is_published',
                true
            )->count(),
        ];
    }

    private function recentActivities()
    {
        return ActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();
    }

    private function monthlyMembers(): array
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {

            $data[] = [
                'month' => $month,

                'count' => Member::whereYear(
                    'created_at',
                    now()->year
                )
                ->whereMonth(
                    'created_at',
                    $month
                )
                ->count(),
            ];
        }

        return $data;
    }
}