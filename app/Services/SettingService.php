<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    private const CACHE_KEY='settings:all';

    public function get(string $key,$default=null)
    {
        $setting=$this->cachedSettings()->firstWhere('key',$key);

        if(!$setting)return $default;

        return $this->castValue(
            $setting->value,
            $setting->type
        );
    }

    public function set(
        string $key,
        mixed $value,
        string $type='string',
        string $group='general',
        ?string $description=null,
        bool $isPublic=false,
        ?array $options=null
    ): Setting{
        $setting=Setting::where('key',$key)->first();

        $attributes=[
            'type'=>$type,
            'group'=>$group,
            'description'=>$description,
            'is_public'=>$isPublic,
            'options'=>$options,
        ];

        if($type==='password'&&($value===null||$value==='')){
            if($setting){
                $setting->update($attributes);
                $this->forgetCache();
                return $setting->fresh();
            }

            $attributes['key']=$key;
            $attributes['value']=null;

            $setting=Setting::create($attributes);
            $this->forgetCache();

            return $setting;
        }

        $attributes['value']=$this->prepareValue($value,$type);

        $setting=Setting::updateOrCreate(
            ['key'=>$key],
            $attributes
        );

        $this->forgetCache();

        return $setting;
    }

    public function all(?string $group=null): Collection
    {
        $settings=$this->cachedSettings();

        if($group){
            $settings=$settings->where('group',$group);
        }

        return $settings
            ->values()
            ->map(fn(Setting $setting)=>$this->redactForOutput($setting));
    }

    public function grouped(): Collection
    {
        return $this->all()->groupBy('group');
    }

    public function publicSettings(): Collection
    {
        return $this->cachedSettings()
            ->where('is_public',true)
            ->values()
            ->map(fn(Setting $setting)=>$this->redactForOutput($setting));
    }

    public function delete(string $key): bool
    {
        $setting=Setting::where('key',$key)->first();

        if(!$setting)return false;

        if(
            $setting->type==='image' &&
            $setting->value &&
            Storage::disk('public')->exists($setting->value)
        ){
            Storage::disk('public')->delete($setting->value);
        }

        $setting->delete();
        $this->forgetCache();

        return true;
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('settings:public');
    }

    protected function cachedSettings(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addHours(6),
            fn()=>Setting::query()
                ->orderBy('group')
                ->orderBy('id')
                ->get()
        );
    }

    protected function redactForOutput(Setting $setting): Setting
    {
        $copy=$setting->replicate();

        $copy->id=$setting->id;
        $copy->created_at=$setting->created_at;
        $copy->updated_at=$setting->updated_at;

        if($setting->type==='password'){
            $copy->value=$setting->getRawOriginal('value')?'':null;
        }

        return $copy;
    }

    protected function castValue(mixed $value,string $type): mixed
    {
        return match($type){
            'boolean'=>filter_var($value,FILTER_VALIDATE_BOOLEAN),
            'integer'=>(int)$value,
            'float'=>(float)$value,
            'json'=>$value?json_decode($value,true):null,
            'password'=>$this->decryptSafely($value),
            default=>$value,
        };
    }

    protected function prepareValue(mixed $value,string $type): ?string
    {
        if($value===null)return null;

        return match($type){
            'boolean'=>$value?'1':'0',
            'json'=>json_encode($value,JSON_UNESCAPED_UNICODE),
            'password'=>Crypt::encryptString((string)$value),
            default=>(string)$value,
        };
    }

    protected function decryptSafely(?string $value): ?string
    {
        if(!$value)return null;

        try{
            return Crypt::decryptString($value);
        }catch(\Throwable){
            return null;
        }
    }
}