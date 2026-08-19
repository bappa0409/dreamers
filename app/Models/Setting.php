<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    protected string $activityLogModule = 'Setting';
    protected string $activityLogLabelColumn = 'key';

    protected $fillable = [
        'key',
        'value',
        'type',
        'options',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'options'   => 'array',
    ];

    /**
     * Never write secret values (SMTP passwords etc.) into the
     * audit log — even encrypted, they don't need to sit in a
     * second table. Everything else about the setting is logged.
     */
    protected function hideActivityLogFields(array $attributes): array
    {
        if (($this->type ?? null) === 'password' || ($attributes['type'] ?? null) === 'password') {
            $attributes['value'] = '••••••••';
        }

        return $attributes;
    }
}