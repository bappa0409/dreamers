<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {
    }

    /**
     * Authenticate user using:
     * - Email
     * - Mobile
     * - Member Code
     */
    public function authenticate(
        string $login,
        string $password
    ): User {
        try {
            return $this->attemptAuthenticate($login, $password);
        } catch (ValidationException $e) {
            $this->activityLogService->log(
                action: 'login_failed',
                module: 'Auth',
                description: 'Failed login attempt for "' . $login . '": ' .
                    collect($e->errors())->flatten()->first(),
            );

            throw $e;
        }
    }

    private function attemptAuthenticate(
        string $login,
        string $password
    ): User {
        $user = User::with([
            'member',
            'roles.permissions',
        ])
            ->where(function ($query) use ($login) {
                $query
                    ->where('email', $login)
                    ->orWhere('mobile', $login)
                    ->orWhereHas(
                        'member',
                        function ($memberQuery) use ($login) {
                            $memberQuery->where(
                                'member_code',
                                $login
                            );
                        }
                    );
            })
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Invalid credentials
        |--------------------------------------------------------------------------
        */

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => [
                    'The email, mobile, member ID or password is incorrect.'
                ],
            ]);
        }
        

        if(!Hash::check($password,$user->password)){
            throw ValidationException::withMessages([
                'login'=>['Invalid login credentials.']
            ]);
        }

        if(!$user->is_active){
            throw ValidationException::withMessages([
                'login'=>[
                    'Your account is not active. Please contact the association administrator.'
                ]
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | User account inactive
        |--------------------------------------------------------------------------
        */

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => [
                    'Your account is inactive.'
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Member profile required
        |--------------------------------------------------------------------------
        */

        if (!$user->member) {
            throw ValidationException::withMessages([
                'login' => [
                    'No member profile is linked with this account.'
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Membership must be active
        |--------------------------------------------------------------------------
        */

        if ($user->member->status !== 'active') {
            throw ValidationException::withMessages([
                'login' => [
                    'Your membership is not active.'
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | At least one role required
        |--------------------------------------------------------------------------
        */

        if ($user->roles->isEmpty()) {
            throw ValidationException::withMessages([
                'login' => [
                    'No system role has been assigned to your account.'
                ],
            ]);
        }

        return $user;
    }

    /**
     * Standard authenticated user payload.
     * This same structure can later be reused by Blade/API/Flutter.
     */
    public function userData(User $user): array
    {
        $user->loadMissing([
            'member',
            'roles.permissions',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Collect permissions from all roles
        |--------------------------------------------------------------------------
        */

        $permissions = $user->roles
            ->flatMap(
                fn ($role) => $role
                    ->permissions
                    ->pluck('name')
            )
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | System Analyst gets full access
        |--------------------------------------------------------------------------
        */

        $allAccess = $user->isSystemAnalyst();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'language' => $user->language,
            'is_active' => (bool) $user->is_active,
            'member' => $user->member,
            'roles' => $user->roles
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                    ];
                })
                ->values(),
            'permissions' => $permissions,
            'is_system_analyst' => $allAccess,
            'all_access' => $allAccess,
        ];
    }
}