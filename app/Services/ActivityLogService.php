<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function log(
        string $action,
        ?string $module = null,
        ?string $description = null,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),

            'action' => $action,

            'module' => $module,

            'description' => $description,

            'subject_type' => $subject
                ? get_class($subject)
                : null,

            'subject_id' => $subject?->getKey(),

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'old_values' => $oldValues,

            'new_values' => $newValues,
        ]);
    }
}