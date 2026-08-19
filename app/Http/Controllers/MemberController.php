<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function dashboard(): View
    {
        return view('member.dashboard');
    }

    public function profile(): View
    {
        return view('member.profile');
    }

    public function investments(): View
    {
        return view('member.investments');
    }

    public function projects(): View
    {
        return view('member.projects');
    }

    public function notices()
    {
        return view('member.notices');
    }

    public function landInvestments(): View
    {
        return view('member.land-investments');
    }

    public function polls(): View
    {
        return view('member.polls');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}