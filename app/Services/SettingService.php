<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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
        $setting=DB::transaction(function()use(
            $key,$value,$type,$group,$description,$isPublic,$options
        ){
            return $this->persist(
                Setting::where('key',$key)->first(),
                $key,
                $value,
                $type,
                $group,
                $description,
                $isPublic,
                $options
            );
        });

        $this->forgetCache();

        return $setting;
    }

    /**
     * Persist a settings screen in one request/transaction. This avoids the
     * previous browser-side N sequential HTTP requests for a single Save.
     */
    public function setMany(array $items): Collection
    {
        if(empty($items)){
            return collect();
        }

        $saved=DB::transaction(function()use($items){
            $keys=collect($items)
                ->pluck('key')
                ->filter()
                ->unique()
                ->values();

            $existing=Setting::query()
                ->whereIn('key',$keys)
                ->get()
                ->keyBy('key');

            return collect($items)->map(function(array $item)use($existing){
                $setting=$this->persist(
                    $existing->get($item['key']),
                    $item['key'],
                    $item['value']??null,
                    $item['type']??'string',
                    $item['group']??'general',
                    $item['description']??null,
                    (bool)($item['is_public']??false),
                    $item['options']??null
                );

                $existing->put($item['key'],$setting);

                return $setting;
            });
        });

        $this->forgetCache();

        return $saved;
    }

    public function all(?string $group=null): Collection
    {
        $settings=$this->cachedSettings();

        if($group){
            $settings=$settings->where('group',$group);
        }

        return $settings
            ->values()
            ->map(fn(Setting $setting)=>$this->redact($setting));
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
            ->map(fn(Setting $setting)=>$this->redact($setting));
    }

    public function redact(Setting $setting): Setting
    {
        $copy=$setting->replicate();

        $copy->id=$setting->id;
        $copy->created_at=$setting->created_at;
        $copy->updated_at=$setting->updated_at;

        if($setting->type==='password'){
            // Never expose encrypted secret material through the settings API.
            $copy->value=$setting->getRawOriginal('value')?'':null;
        }

        return $copy;
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

    protected function persist(
        ?Setting $setting,
        string $key,
        mixed $value,
        string $type,
        string $group,
        ?string $description,
        bool $isPublic,
        ?array $options
    ): Setting{
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
                return $setting->fresh();
            }

            return Setting::create([
                'key'=>$key,
                ...$attributes,
                'value'=>null,
            ]);
        }

        $attributes['value']=$this->prepareValue($value,$type);

        if($setting){
            $setting->update($attributes);
            return $setting->fresh();
        }

        return Setting::create([
            'key'=>$key,
            ...$attributes,
        ]);
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

    protected function castValue(mixed $value,string $type): mixed
    {
        return match($type){
            'boolean'=>filter_var($value,FILTER_VALIDATE_BOOLEAN),
            'integer'=>(int)$value,
            'float','number'=>(float)$value,
            'json'=>$value?json_decode($value,true):null,
            'password'=>$this->decryptSafely($value),
            default=>$value,
        };
    }

    protected function prepareValue(mixed $value,string $type): ?string
    {
        if($value===null)return null;

        return match($type){
            'boolean'=>filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN
            )?'1':'0',
            'json'=>is_string($value)
                ?$value
                :json_encode($value,JSON_UNESCAPED_UNICODE),
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
