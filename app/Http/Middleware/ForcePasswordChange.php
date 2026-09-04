<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Route names a user who still must change their password is allowed
     * to reach. Everything else (dashboard, admin.*, member.*, direct URLs,
     * etc.) redirects back to the forced password-change page, even if the
     * user types the URL directly.
     */
    protected array $exceptRouteNames = [
        'password.force-change',
        'password.force-change.submit',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (
            $user &&
            $user->must_change_password &&
            !in_array(
                $request->route()?->getName(),
                $this->exceptRouteNames,
                true
            )
        ) {
            return redirect()->route('password.force-change');
        }

        return $next($request);
    }
}
