<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ){}

    public function index(Request $request): View
    {
        $user=$request->user();

        $user->loadMissing([
            'member',
            'roles.permissions'
        ]);

        return view('admin.dashboard',[
            'user'=>$user,
            'member'=>$user->member,
            'dashboard'=>$this->dashboardService->getDashboardData($user),
        ]);
    }
}