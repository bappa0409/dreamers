<?php

namespace App\Providers;

use App\Services\ActivityLogService;
use App\Services\SettingService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login',function(Request $request){
            $attempts=5;

            try{
                $attempts=(int)app(SettingService::class)->get(
                    'max_login_attempts',
                    5
                );
            }catch(\Throwable){
                // Keep authentication available with a safe default while
                // settings/database initialization is in progress.
            }

            $attempts=max(1,min($attempts,50));

            return Limit::perMinute($attempts)->by(
                strtolower(trim((string)$request->input('login')))
                .'|'.$request->ip()
            );
        });

        $this->registerActivityLogListeners();

        /*
        |--------------------------------------------------------------------------
        | Database Settings
        |--------------------------------------------------------------------------
        |
        | The settings table may not exist while installing/migrating. The table
        | check itself can also throw when the configured database has not been
        | created yet, so keep the whole bootstrap lookup guarded.
        |
        */
        try{
            if(!Schema::hasTable('settings')){
                return;
            }

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

            $sessionLifetime=(int)$settingService->get(
                'session_lifetime_minutes',
                config('session.lifetime',120)
            );

            Config::set(
                'session.lifetime',
                max(5,min($sessionLifetime,43200))
            );

            /*
            |--------------------------------------------------------------------------
            | Mail
            |--------------------------------------------------------------------------
            */
            $mailer=$settingService->get('mail_mailer');

            if(is_string($mailer)&&trim($mailer)!==''){
                Config::set('mail.default',trim($mailer));
            }

            $fromAddress=$settingService->get('mail_from_address');
            if(is_string($fromAddress)&&trim($fromAddress)!==''){
                Config::set('mail.from.address',trim($fromAddress));
            }

            $fromName=$settingService->get('mail_from_name');
            if(is_string($fromName)&&trim($fromName)!==''){
                Config::set('mail.from.name',trim($fromName));
            }else{
                Config::set('mail.from.name',$organizationName);
            }

            if(config('mail.default')==='smtp'){
                $smtpHost=$settingService->get('smtp_host');
                $smtpPort=$settingService->get('smtp_port');
                $smtpUsername=$settingService->get('smtp_username');
                $smtpPassword=$settingService->get('smtp_password');
                $smtpEncryption=strtolower(
                    trim((string)$settingService->get('smtp_encryption',''))
                );

                if(is_string($smtpHost)&&trim($smtpHost)!==''){
                    Config::set('mail.mailers.smtp.host',trim($smtpHost));
                }

                if($smtpPort!==null&&$smtpPort!==''){
                    Config::set('mail.mailers.smtp.port',(int)$smtpPort);
                }

                if(is_string($smtpUsername)&&trim($smtpUsername)!==''){
                    Config::set('mail.mailers.smtp.username',trim($smtpUsername));
                }

                if(is_string($smtpPassword)&&$smtpPassword!==''){
                    Config::set('mail.mailers.smtp.password',$smtpPassword);
                }

                $scheme=match($smtpEncryption){
                    'ssl','smtps'=>'smtps',
                    'tls','starttls'=>'smtp',
                    'none',''=>config('mail.mailers.smtp.scheme'),
                    default=>config('mail.mailers.smtp.scheme'),
                };

                Config::set('mail.mailers.smtp.scheme',$scheme);
            }

        }catch(\Throwable $e){
            /*
             * Never break application bootstrap solely because optional database
             * settings are temporarily unavailable. Normal DB operations will
             * still surface their own errors when the application actually uses
             * the unavailable connection.
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
