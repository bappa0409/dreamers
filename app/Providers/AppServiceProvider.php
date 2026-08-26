<?php

namespace App\Providers;

use App\Services\ActivityLogService;
use App\Services\SettingService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {

        RateLimiter::for('login',function(Request $request){
            return Limit::perMinute(10)->by(
                strtolower((string)$request->input('login')).'|'.$request->ip()
            );
        });

        $this->registerActivityLogListeners();

        /*
        |--------------------------------------------------------------------------
        | Database Settings
        |--------------------------------------------------------------------------
        |
        | During migrations the settings table may not exist yet.
        |
        */
        if(!Schema::hasTable('settings')){
            return;
        }

        try{
            $settingService=app(SettingService::class);

            /*
            |--------------------------------------------------------------------------
            | Application
            |--------------------------------------------------------------------------
            */
            $organizationName=$settingService->get(
                'organization_name',
                config('app.name')
            );

            $timezone=$settingService->get(
                'timezone',
                config('app.timezone','Asia/Dhaka')
            );

            Config::set('app.name',$organizationName);
            Config::set('app.timezone',$timezone);

            if($timezone){
                date_default_timezone_set($timezone);
            }

            /*
            |--------------------------------------------------------------------------
            | Mail
            |--------------------------------------------------------------------------
            */
            $mailer=$settingService->get(
                'mail_mailer',
                config('mail.default')
            );

            Config::set('mail.default',$mailer);

            Config::set(
                'mail.from.address',
                $settingService->get(
                    'mail_from_address',
                    config('mail.from.address')
                )
            );

            Config::set(
                'mail.from.name',
                $settingService->get(
                    'mail_from_name',
                    $organizationName
                )
            );

            if($mailer==='smtp'){
                Config::set(
                    'mail.mailers.smtp.host',
                    $settingService->get(
                        'smtp_host',
                        config('mail.mailers.smtp.host')
                    )
                );

                Config::set(
                    'mail.mailers.smtp.port',
                    (int)$settingService->get(
                        'smtp_port',
                        config('mail.mailers.smtp.port',587)
                    )
                );

                Config::set(
                    'mail.mailers.smtp.username',
                    $settingService->get(
                        'smtp_username',
                        config('mail.mailers.smtp.username')
                    )
                );

                Config::set(
                    'mail.mailers.smtp.password',
                    $settingService->get(
                        'smtp_password',
                        config('mail.mailers.smtp.password')
                    )
                );

                $encryption=$settingService->get(
                    'smtp_encryption',
                    config('mail.mailers.smtp.encryption')
                );

                Config::set(
                    'mail.mailers.smtp.encryption',
                    $encryption==='none'
                        ?null
                        :$encryption
                );
            }

        }catch(\Throwable $e){
            /*
             * Never break the application because a setting is
             * temporarily unavailable.
             */
            report($e);
        }
    }

    private function registerActivityLogListeners(): void
    {
        Event::listen(Login::class,function(Login $event){
            app(ActivityLogService::class)->log(
                action:'login',
                module:'Auth',
                description:"{$event->user->name} logged in",
                subject:$event->user
            );
        });

        Event::listen(Logout::class,function(Logout $event){
            if(!$event->user)return;

            app(ActivityLogService::class)->log(
                action:'logout',
                module:'Auth',
                description:"{$event->user->name} logged out",
                subject:$event->user
            );
        });

        Event::listen(Failed::class,function(Failed $event){
            $login=
                $event->credentials['login']
                ??$event->credentials['email']
                ??'unknown';

            app(ActivityLogService::class)->log(
                action:'login_failed',
                module:'Auth',
                description:"Failed login attempt for \"{$login}\"",
                subject:$event->user
            );
        });
    }
}