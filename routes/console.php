<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('auth:clear-expired-setup-tokens')
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::command('database:backup')
    ->dailyAt('02:30')
    ->when(
        fn() => (bool)setting(
            'automatic_backup_enabled',
            true
        )
    )
    ->withoutOverlapping();

Schedule::command('subscriptions:generate')
    ->monthlyOn(1, '00:10')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('subscriptions:generate-dues')
    ->monthlyOn(1, '00:05')
    ->withoutOverlapping()
    ->onOneServer();

    Schedule::command('subscriptions:apply-fines')
    ->dailyAt('00:15')
    ->withoutOverlapping();

    Schedule::command('subscriptions:process')
    ->dailyAt('00:10')
    ->withoutOverlapping()
    ->onOneServer();
