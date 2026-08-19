<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Show "Forgot Password" Form
    |--------------------------------------------------------------------------
    */

    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /*
    |--------------------------------------------------------------------------
    | Send Reset Link
    |--------------------------------------------------------------------------
    */

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        /*
        | Only send if the account exists and is active.
        | The response message is identical either way so we
        | don't leak which emails exist in the system.
        */

        if ($user && $user->is_active) {
            $this->passwordResetService->send($user);
        }

        return back()->with(
            'success',
            'If an account exists for that email, a password reset link has been sent.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Reset Password Form
    |--------------------------------------------------------------------------
    */

    public function showResetForm(Request $request): View
    {
        $token = (string) $request->query('token', '');
        $email = (string) $request->query('email', '');

        if (!$token || !$email) {
            abort(404);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    */

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!$this->passwordResetService->isValid($validated['email'], $validated['token'])) {
            throw ValidationException::withMessages([
                'token' => ['This password reset link is invalid or has expired.'],
            ]);
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['We could not find an account with that email.'],
            ]);
        }

        $this->passwordResetService->reset($user, $validated['password']);

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Your password has been reset successfully. Please log in.'
            );
    }
}