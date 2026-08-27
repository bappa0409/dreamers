<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ){}

    public function showLogin(): View|RedirectResponse
    {
        if(Auth::check()){
            return $this->redirectAfterLogin(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials=$request->validate([
            'login'=>['required','string','max:255'],
            'password'=>['required','string','max:255'],
            'remember'=>['nullable','boolean'],
        ]);

        try{
            $user=$this->authService->authenticate(
                $credentials['login'],
                $credentials['password']
            );
        }catch(ValidationException $exception){
            return back()
                ->withErrors($exception->errors())
                ->withInput($request->only(['login','remember']));
        }

        // Auth::login dispatches Laravel's Login event. AppServiceProvider
        // records that event in the audit log, so do not write a duplicate
        // activity row here.
        Auth::login($user,$request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectAfterLogin($user,true);
    }

    public function logout(Request $request): RedirectResponse
    {
        // Auth::logout dispatches Laravel's Logout event. The centralized
        // listener records it once in the audit log.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectAfterLogin($user,bool $intended=false): RedirectResponse
    {
        $user->loadMissing(['member','roles']);

        $isActiveMember=$user->member?->status==='active';
        $isOnlyMember=$user->roles->isNotEmpty()
            &&$user->roles->every(fn($role)=>$role->name==='member');

        if($isActiveMember&&$isOnlyMember){
            // Do not honor an old intended admin URL for a member-only account.
            return redirect()->route('member.dashboard');
        }

        $route=route('dashboard');

        return $intended
            ?redirect()->intended($route)
            :redirect()->to($route);
    }
}
