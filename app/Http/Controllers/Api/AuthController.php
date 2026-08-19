<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Services\ActivityLogService;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected ActivityLogService $activityLogService
    ) {
    }


    /**
     * Register a normal member account.
     *
     * Email OR mobile is required.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255','unique:users,email'],
            'mobile' => ['nullable','string','max:20','regex:/^[0-9+()\-\s]+$/','unique:users,mobile'],
            'password' => ['required','string',Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),'confirmed',],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Email OR mobile is required
        |--------------------------------------------------------------------------
        */

        if (
            empty($validated['email']) &&
            empty($validated['mobile'])
        ) {
            throw ValidationException::withMessages([
                'email' => ['Please provide either an email address or mobile number.'],
                'mobile' => ['Please provide either an email address or mobile number.'],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Default member role
        |--------------------------------------------------------------------------
        */

        $memberRole = Role::where('name','member')->first();

        if (!$memberRole) {
            return response()->json([
                'success' => false,
                'message' => 'Member role is not configured.',
            ], 500);
        }


        /*
        |--------------------------------------------------------------------------
        | Create user
        |--------------------------------------------------------------------------
        */

        $user = User::create([

            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make(
                $validated['password']
            ),
            'role_id' => $memberRole->id,
            'is_active' => true,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Create API token
        |--------------------------------------------------------------------------
        */

        $token = $user->createToken(
            $request->input('device_name','api')
        )->plainTextToken;


        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'token' => $token,
                'user' => $this->authService->userData($user),
            ],

        ], 201);
    }


    /**
     * Login using email OR mobile.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required','string','max:255'],
            'password' => ['required','string'],
        ]);

        try {
            $user = $this->authService->authenticate(
                $credentials['login'],
                $credentials['password']
            );

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'The email/mobile or password is incorrect.',
                'errors' => $e->errors(),
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Remove old tokens
        |--------------------------------------------------------------------------
        */

        $user->tokens()->delete();


        /*
        |--------------------------------------------------------------------------
        | Create new token
        |--------------------------------------------------------------------------
        */

        $token = $user->createToken(
            $request->input('device_name','api')
        )->plainTextToken;

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        |
        | AuthController never calls Auth::login(), so the Login
        | event listener in AppServiceProvider never fires for the
        | API/SPA token flow — log it explicitly here instead.
        |
        */

        $this->activityLogService->log(
            action: 'login',
            module: 'Auth',
            description: "{$user->name} logged in",
            subject: $user,
        );


        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'user' => $this->authService->userData($user),
            ],
        ]);
    }


    /**
     * Logout authenticated user.
     */
    public function logout(Request $request)
    {
        $token = $request->user()
            ->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        $this->activityLogService->log(
            action: 'logout',
            module: 'Auth',
            description: "{$user->name} logged out",
            subject: $user,
        );

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
        ]);
    }


    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' =>$this->authService->userData($request->user()),
        ]);
    }
}