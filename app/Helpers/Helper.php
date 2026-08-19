<?php

use App\Services\SettingService;

if(!function_exists('setting')){
    function setting(string $key,mixed $default=null): mixed
    {
        return app(SettingService::class)->get($key,$default);
    }
}

if(!function_exists('money')){
    function money(float|int|string|null $amount): string
    {
        $symbol=setting('currency_symbol','৳');

        return $symbol.number_format(
            (float)($amount??0),
            2
        );
    }
}

if(!function_exists('app_date')){
    function app_date($date): ?string
    {
        if(!$date)return null;

        return \Carbon\Carbon::parse($date)->format(
            setting('date_format','d-m-Y')
        );
    }
}

if(!function_exists('app_datetime')){
    function app_datetime($date): ?string
    {
        if(!$date)return null;

        $format=setting('date_format','d-m-Y');
        $timeFormat=setting('time_format','12')==='24'
            ?'H:i'
            :'h:i A';

        return \Carbon\Carbon::parse($date)
            ->format("{$format} {$timeFormat}");
    }
}