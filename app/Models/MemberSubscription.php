<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberSubscription extends Model
{
    use HasFactory, LogsActivity;

    protected string $activityLogModule='Member Subscription';
    protected string $activityLogLabelColumn='id';

    protected $fillable=[
        'member_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return[
            'start_date'=>'date',
            'end_date'=>'date',
            'is_active'=>'boolean',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(
            Member::class
        );
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'subscription_plan_id'
        );
    }

    public function dues(): HasMany
    {
        return $this->hasMany(
            SubscriptionDue::class,
            'member_subscription_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }
}