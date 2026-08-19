<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use App\Services\SettingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            try {
                $settingService = app(SettingService::class);

                Config::set('mail.default', $settingService->get('mail_mailer', config('mail.default')));
                Config::set('mail.mailers.smtp.host', $settingService->get('smtp_host', config('mail.mailers.smtp.host')));
                Config::set('mail.mailers.smtp.port', $settingService->get('smtp_port', config('mail.mailers.smtp.port')));
                Config::set('mail.mailers.smtp.username', $settingService->get('smtp_username', config('mail.mailers.smtp.username')));
                Config::set('mail.mailers.smtp.password', $settingService->get('smtp_password', config('mail.mailers.smtp.password')));
                Config::set('mail.mailers.smtp.encryption', $settingService->get('smtp_encryption', config('mail.mailers.smtp.encryption')));
                Config::set('mail.from.address', $settingService->get('mail_from_address', config('mail.from.address')));
                Config::set('mail.from.name', $settingService->get('mail_from_name', config('mail.from.name')));
            } catch (\Exception $e) {
                // Settings table may not exist yet during migrations — fail silently.
            }
        }
    }
}
