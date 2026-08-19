<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class SettingService
{
    public function get(string $key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $this->castValue(
            $setting->value,
            $setting->type
        );
    }


    public function set(
        string $key,
        $value,
        string $type = 'string',
        string $group = 'general',
        ?string $description = null,
        bool $isPublic = false
    ): Setting {

        $attributes = [
            'type' => $type,
            'group' => $group,
            'description' => $description,
            'is_public' => $isPublic,
        ];

        /*
        |--------------------------------------------------------------------------
        | Password Fields
        |--------------------------------------------------------------------------
        |
        | An empty submitted value means "keep the existing secret",
        | not "clear it". This avoids wiping out SMTP passwords etc.
        | when the admin re-saves a form without retyping them.
        |
        */

        if ($type === 'password' && ($value === null || $value === '')) {

            $existing = Setting::where('key', $key)->first();

            if (!$existing) {
                return Setting::create(
                    array_merge($attributes, [
                        'key' => $key,
                        'value' => null,
                    ])
                );
            }

            $existing->update($attributes);

            return $existing;
        }

        $attributes['value'] = $this->prepareValue($value, $type);

        return Setting::updateOrCreate(
            ['key' => $key],
            $attributes
        );
    }


    public function all(?string $group = null)
    {
        $query = Setting::query()
            ->orderBy('group')
            ->orderBy('key');

        if ($group) {
            $query->where('group', $group);
        }

        return $query->get()->map(
            fn ($setting) => $this->redactForOutput($setting)
        );
    }


    public function publicSettings()
    {
        return Setting::where(
            'is_public',
            true
        )
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->map(
                fn ($setting) => $this->redactForOutput($setting)
            );
    }


    /**
     * Never leak encrypted secrets to the frontend.
     * '' means "a value exists but is hidden",
     * null means "not set yet".
     */
    private function redactForOutput(Setting $setting): Setting
    {
        if ($setting->type === 'password') {
            $setting = $setting->replicate();
            $setting->value = $setting->getRawOriginal('value') ? '' : null;
        }

        return $setting;
    }


    private function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN
            ),

            'integer' => (int) $value,

            'float' => (float) $value,

            'json' => json_decode(
                $value,
                true
            ),

            'password' => $this->decryptSafely($value),

            default => $value,
        };
    }


    private function prepareValue($value, string $type)
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',

            'json' => json_encode(
                $value
            ),

            'password' => Crypt::encryptString(
                (string) $value
            ),

            default => (string) $value,
        };
    }


    private function decryptSafely(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}