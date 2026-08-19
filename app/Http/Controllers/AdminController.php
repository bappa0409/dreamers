<?php

namespace App\Http\Controllers;

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

    public function userRoles()
    {
        return view('admin.users.roles');
    }

    public function backups(): View
    {
        return view('admin.backups.index');
    }
    
    public function createMember(): View
    {
        return view('admin.members.create');
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
    public function projects(): View
    {
        return view('admin.projects.index');
    }

    public function polls(): View
    {
        return view('admin.polls.index');
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

    public function mailing(): View
    {
        return view('admin.mailing.index');
    }

    public function users(): View
    {
        return view('admin.users.index');
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
