<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $settingService = app(SettingService::class);

        $isMaintenanceOn = (bool) $settingService->get(
            'maintenance_mode',
            false
        );

        if (!$isMaintenanceOn) {
            return $next($request);
        }

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | System Analysts / logged-in admins bypass maintenance
        |--------------------------------------------------------------------------
        */

        if ($user && $user->isSystemAnalyst()) {
            return $next($request);
        }

        $message = $settingService->get(
            'maintenance_message',
            'We are performing scheduled maintenance. Please check back shortly.'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 503);
        }

        return response()->view('errors.maintenance', [
            'message' => $message,
        ], 503);
    }
}