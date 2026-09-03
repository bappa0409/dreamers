<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function members(): View
    {
        return view('admin.members.index');
    }

    public function welfare(): View
    {
        return view('admin.welfare.index');
    }

    public function feedbackSupport(): View
    {
        return view(
            'admin.feedback-support.index'
        );
    }
    public function memberExits(): View
    {
        return view('admin.member-exits.index');
    }

    public function contactMessages(): View
    {
        return view('admin.contact-messages.index');
    }

    public function userRoles()
    {
        return view('admin.users.roles');
    }

    public function backups(): View
    {
        return view('admin.backups.index');
    }

    public function createMember(): RedirectResponse
    {
        return redirect()->route('admin.members');
    }

    public function investments(): View
    {
        return view('admin.investments.index');
    }

    public function land(): View
    {
        return view('admin.land.index');
    }

    public function finance()
    {
        return view('admin.finance.index');
    }

    public function incomes()
    {
        return view('admin.finance.incomes.index');
    }

    public function expenses()
    {
        return view('admin.finance.expenses.index');
    }

    public function charges(): View
    {
        return view('admin.finance.charges.index');
    }

    public function assets(): View
    {
        return view('admin.finance.assets.index');
    }

    public function accounts(): View
    {
        return view(
            'admin.finance.accounts.index'
        );
    }

    public function journals(): View
    {
        return view('admin.finance.journals.index');
    }

    public function ledger(): View
    {
        return view(
            'admin.finance.ledger.index'
        );
    }

    public function trialBalance(): View
    {
        return view(
            'admin.finance.reports.trial-balance'
        );
    }

    public function balanceSheet(): View
    {
        return view(
            'admin.finance.reports.balance-sheet'
        );
    }

    public function profitLoss(): View
    {
        return view(
            'admin.finance.reports.profit-loss'
        );
    }

    public function subscriptionPayments()
    {
        return view('admin.finance.subscription-payments.index');
    }

    public function sharePurchases()
    {
        return view('admin.finance.share-purchases.index');
    }

    public function projects(): View
    {
        return view('admin.projects.index');
    }

    public function polls(): View
    {
        return view('admin.polls.index');
    }

    public function tours()
    {
        return view('admin.tours.index');
    }

    public function meetings()
    {
        return view('admin.meetings.index');
    }

    public function loans(): View
    {
        return view('admin.loans.index');
    }

    public function nominees(): View
    {
        return view('admin.nominees.index');
    }
    public function notices(): View
    {
        return view('admin.notices.index');
    }

    public function notifications(): View
    {
        return view('admin.notifications.index');
    }

    public function reports(): View
    {
        return view('admin.reports.index');
    }

    public function documents(): View
    {
        return view('admin.documents.index');
    }

    public function approvals(): View
    {
        return view('admin.approvals.index');
    }

    public function approvalWorkflows(): View
    {
        return view('admin.approvals.workflows');
    }

    public function mailing(): View
    {
        return view('admin.mailing.index');
    }

    public function users(): View
    {
        return view('admin.users.index');
    }
    
    public function subscriptionPlans()
    {
        return view('admin.subscription-plans.index');
    }

    public function landingPage(): View
    {
        return view('admin.landing-page.index');
    }

    public function settings(): View
    {
        return view('admin.settings.index');
    }

    public function activityLogs(): View
    {
        return view('admin.activity-logs.index');
    }

    public function roles(): View
    {
        return view('admin.roles.index');
    }
}
