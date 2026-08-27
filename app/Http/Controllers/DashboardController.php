<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ){}

    public function index(Request $request): View|RedirectResponse
    {
        $user=$request->user();

        $user->loadMissing([
            'member',
            'roles.permissions'
        ]);

        /*
        | Member-only accounts must never render the admin dashboard, even
        | when /dashboard is typed manually or reached from an old link.
        */
        $isOnlyMember=$user->roles->isNotEmpty()
            &&$user->roles->every(fn($role)=>$role->name==='member');

        if($isOnlyMember){
            return redirect()->route('member.dashboard');
        }

        return view('admin.dashboard',[
            'user'=>$user,
            'member'=>$user->member,
            'dashboard'=>$this->dashboardService->getDashboardData($user),
        ]);
    }
}
