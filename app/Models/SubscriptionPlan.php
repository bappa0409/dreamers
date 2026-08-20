<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Subscription';
    protected string $activityLogLabelColumn='name';

    protected $fillable=[
        'name',
        'amount',
        'due_day',
        'fine_type',
        'fine_value',
        'grace_days',
        'is_default',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'due_day'=>'integer',
            'fine_value'=>'decimal:2',
            'grace_days'=>'integer',
            'is_default'=>'boolean',
            'is_active'=>'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }
}