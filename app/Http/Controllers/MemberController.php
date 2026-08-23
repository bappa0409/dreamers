<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Services\MemberDashboardService;

class MemberController extends Controller
{
    public function __construct(
        protected MemberDashboardService $memberDashboardService
    ) {}

    public function dashboard(): View
    {
        $user = auth()->user();

        $member = $user->member;

        abort_unless(
            $member && $member->status === 'active',
            403
        );

        return view('member.dashboard', [
            'member' => $member,
            'summary' => $this->memberDashboardService
                ->summary($member),
        ]);
    }

    public function profile(): View
    {
        return view('member.profile');
    }

    public function polls(): View
    {
        return view('member.polls');
    }

    public function notices(): View
    {
        return view('member.notices');
    }

    public function subscriptionPayments()
    {
        return view('member.subscriptions.subscription-payments');
    }

    public function investments()
    {
        return view('member.investments',['investmentStatus' => '',]);
    }

    public function activeInvestments()
    {
        return view('member.investments',['investmentStatus' => 'active',]);
    }

    public function completedInvestments()
    {
        return view('member.investments',['investmentStatus' => 'completed',]);
    }

    public function notifications()
{
    return view('member.notifications');
}
    public function shares(): View
    {
        abort_unless(
            filter_var(
                setting('share_enabled', false),
                FILTER_VALIDATE_BOOLEAN
            ),
            404
        );

        return view('member.shares');
    }

    public function subscriptions(): View
    {
        return view('member.subscriptions.monthly-due');
    }
}
