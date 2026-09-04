<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ForcePasswordChangeController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user->must_change_password) {
            return $this->redirectAfterChange($user);
        }

        return view('auth.force-password-change');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        $user->tokens()->delete();

        return $this->redirectAfterChange(
            $user,
            'Password changed successfully.'
        );
    }

    private function redirectAfterChange(
        $user,
        ?string $success = null
    ): RedirectResponse {
        $user->loadMissing(['member', 'roles']);

        $isActiveMember = $user->member?->status === 'active';
        $isOnlyMember = $user->roles->isNotEmpty()
            && $user->roles->every(fn($role) => $role->name === 'member');

        $route = $isActiveMember && $isOnlyMember
            ? 'member.dashboard'
            : 'dashboard';

        $redirect = redirect()->route($route);

        return $success
            ? $redirect->with('success', $success)
            : $redirect;
    }
}
