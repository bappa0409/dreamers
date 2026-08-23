<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\MemberMiddleware;
use App\Http\Middleware\CheckMaintenanceMode;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function(){
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/member.php'));
        },
    )
    ->withMiddleware(function(Middleware $middleware): void{
        $middleware->statefulApi();

        $middleware->alias([
            'permission'=>PermissionMiddleware::class,
            'member'=>MemberMiddleware::class,
            'maintenance'=>CheckMaintenanceMode::class,
        ]);

        $middleware->web(append:[
            CheckMaintenanceMode::class,
        ]);
    })
    ->withExceptions(function(Exceptions $exceptions): void{
        //
    })
    ->create();