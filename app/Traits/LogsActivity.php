<?php

namespace App\Traits;

use App\Services\ActivityLogService;

/**
 * Attach to any Eloquent model to automatically record
 * create / update / delete events in the audit log.
 *
 * Optional overrides on the model:
 *
 *   protected string $activityLogModule = 'Member';
 *   protected array  $activityLogHidden = ['password'];
 *   protected string $activityLogLabelColumn = 'name';
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        // Never log while seeding / migrating / running artisan
        // commands from the console — this would flood the audit
        // log with thousands of rows for a single `db:seed` run.
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        static::created(function ($model) {
            $model->recordActivity(
                'created',
                null,
                $model->activityLogAttributes()
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $old = collect($changes)
                ->keys()
                ->mapWithKeys(fn ($key) => [
                    $key => $model->getOriginal($key),
                ])
                ->toArray();

            $model->recordActivity(
                'updated',
                $model->hideActivityLogFields($old),
                $model->hideActivityLogFields($changes)
            );
        });

        static::deleted(function ($model) {
            $model->recordActivity(
                'deleted',
                $model->activityLogAttributes(),
                null
            );
        });
    }

    protected function recordActivity(string $action, ?array $old, ?array $new): void
    {
        app(ActivityLogService::class)->log(
            action: $action,
            module: $this->activityLogModule(),
            description: $this->activityLogDescription($action),
            subject: $this,
            oldValues: $old,
            newValues: $new,
        );
    }

    protected function activityLogModule(): string
    {
        return $this->activityLogModule ?? class_basename($this);
    }

    protected function activityLogDescription(string $action): string
    {
        $label = $this->activityLogLabel();

        return trim(
            ucfirst($action) . ' ' . $this->activityLogModule() .
            ($label ? " \"{$label}\"" : " #{$this->getKey()}")
        );
    }

    protected function activityLogLabel(): ?string
    {
        $column = $this->activityLogLabelColumn ?? null;

        if ($column && isset($this->{$column})) {
            return (string) $this->{$column};
        }

        foreach (['name', 'title', 'display_name', 'key'] as $guess) {
            if (isset($this->{$guess})) {
                return (string) $this->{$guess};
            }
        }

        return null;
    }

    protected function activityLogAttributes(): array
    {
        return $this->hideActivityLogFields(
            collect($this->getAttributes())->toArray()
        );
    }

    protected function hideActivityLogFields(array $attributes): array
    {
        $hidden = $this->activityLogHidden ?? [
            'password',
            'remember_token',
            'password_setup_token',
        ];

        return collect($attributes)->except($hidden)->toArray();
    }
}
