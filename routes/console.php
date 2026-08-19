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
        fn()=>(bool)setting(
            'automatic_backup_enabled',
            true
        )
    )
    ->withoutOverlapping();