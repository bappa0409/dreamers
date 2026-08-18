<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Authentication Check
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('login');
        }


        /*
        |--------------------------------------------------------------------------
        | Member Check
        |--------------------------------------------------------------------------
        */

        $member = $user->member;

        if (!$member) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not registered as a member.',
                ], 403);
            }

            abort(403, 'You are not registered as a member.');
        }


        /*
        |--------------------------------------------------------------------------
        | Active Membership Check
        |--------------------------------------------------------------------------
        */

        if ($member->status !== 'active') {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your membership is not active.',
                    'status' => $member->status,
                ], 403);
            }

            abort(
                403,
                'Your membership is not active.'
            );
        }


        return $next($request);
    }
}