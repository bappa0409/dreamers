<?php

use App\Services\LoanService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\ActivityLog;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('auth:clear-expired-setup-tokens')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();

$backupFrequency='daily';

try{
    $backupFrequency=strtolower(
        trim((string)setting('backup_frequency','daily'))
    );
}catch(\Throwable){
    // Settings table may not exist yet (fresh install/migrating).
    // Keep command discovery working with a safe default.
}

$backupSchedule=Schedule::command('database:backup')
    ->when(fn() => (bool)setting('automatic_backup_enabled', true));

match($backupFrequency){
    'weekly'=>$backupSchedule->weeklyOn(0,'02:30'),
    'monthly'=>$backupSchedule->monthlyOn(1,'02:30'),
    default=>$backupSchedule->dailyAt('02:30'),
};

$backupSchedule
    ->withoutOverlapping()
    ->onOneServer();

// Pre-generate next month's dues a few days early (configurable day),
// so members can see/pay upcoming dues in advance.
$subscriptionGenerateDay = 25;

try{
    $subscriptionGenerateDay = min(max((int)setting('subscription_generate_day', 25), 1), 28);
}catch(\Throwable){
    // Settings table may not exist yet (fresh install/migrating).
    // Keep command discovery working with a safe default.
}
Schedule::call(function () {
    $next = now()->addMonthNoOverflow();

    Artisan::call('subscriptions:generate-dues', [
        '--year' => $next->year,
        '--month' => $next->month,
    ]);
})
    ->name('subscriptions:generate-dues-next-month')
    ->monthlyOn($subscriptionGenerateDay, '00:01')
    ->withoutOverlapping()
    ->onOneServer();

// Fallback: generate current month's dues on day 1 for any subscription
// that wasn't covered by the early-generation run above (e.g. created
// or reactivated after the pre-generation date).
Schedule::command('subscriptions:generate-dues')
    ->monthlyOn(1, '00:05')
    ->withoutOverlapping()
    ->onOneServer();

// Apply late fines to overdue dues. This is the single place fines are
// applied — do not also schedule subscriptions:process, which duplicates
// this same call.
Schedule::command('subscriptions:apply-fines')
    ->dailyAt('00:15')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::call(function () {
    app(LoanService::class)->markOverdueLoans();
})
    ->name('loans:mark-overdue')
    ->dailyAt('00:10')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::call(function () {
    ActivityLog::where('created_at', '<', now()->subMonths(6))->delete();
})
    ->name('activity-log:prune')
    ->monthly()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('queue:prune-failed')->weekly();