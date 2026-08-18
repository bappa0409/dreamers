<?php

namespace App\Services;

use App\Models\Setting;

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
        return Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->prepareValue(
                    $value,
                    $type
                ),
                'type' => $type,
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic,
            ]
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

        return $query->get();
    }


    public function publicSettings()
    {
        return Setting::where(
            'is_public',
            true
        )
            ->orderBy('group')
            ->orderBy('key')
            ->get();
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

            default => (string) $value,
        };
    }
}