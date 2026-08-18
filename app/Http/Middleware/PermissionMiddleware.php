<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$permissions
    ): Response {

        $user = $request->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()
                ->route('login');
        }


        /*
        |--------------------------------------------------------------------------
        | No Permission Requirement
        |--------------------------------------------------------------------------
        */

        if (empty($permissions)) {
            return $next($request);
        }


        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        if (!$user->hasAnyPermission($permissions)) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,

                    'message' =>
                        'You do not have permission to perform this action.',
                ], 403);
            }


            abort(
                403,
                'You do not have permission to access this page.'
            );
        }


        return $next($request);
    }
}