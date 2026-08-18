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
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Show Login Page
    |--------------------------------------------------------------------------
    */

    public function showLogin(): View|RedirectResponse
    {
        /*
         * Already authenticated users should
         * not see the login screen again.
         */

        if (Auth::check()) {
            return redirect()
                ->route('dashboard');
        }

        return view('auth.login');
    }


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Validate Input
        |--------------------------------------------------------------------------
        */

        $credentials = $request->validate([
            'login' => [
                'required',
                'string',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
                'max:255',
            ],

            'remember' => [
                'nullable',
                'boolean',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Authenticate
        |--------------------------------------------------------------------------
        */

        try {

            $user = $this->authService
                ->authenticate(
                    $credentials['login'],
                    $credentials['password']
                );

        } catch (ValidationException $exception) {

            return back()
                ->withErrors(
                    $exception->errors()
                )
                ->withInput(
                    $request->only([
                        'login',
                        'remember',
                    ])
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Login User
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            $request->boolean('remember')
        );


        /*
        |--------------------------------------------------------------------------
        | Session Fixation Protection
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Unified Dashboard
        |--------------------------------------------------------------------------
        |
        | System Analyst
        | Admin
        | Teller
        | Member
        |
        | Everyone enters the same dashboard.
        |
        */

        return redirect()
            ->intended(
                route('dashboard')
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(
        Request $request
    ): RedirectResponse {

        Auth::logout();


        /*
        |--------------------------------------------------------------------------
        | Destroy Current Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->invalidate();


        /*
        |--------------------------------------------------------------------------
        | Regenerate CSRF Token
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerateToken();


        return redirect()
            ->route('login');
    }
}