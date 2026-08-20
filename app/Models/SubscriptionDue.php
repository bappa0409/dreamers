<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionDue extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Monthly Subscription';
    protected string $activityLogLabelColumn='id';

    protected $fillable=[
        'member_subscription_id',
        'year',
        'month',
        'base_amount',
        'share_count',
        'fine_amount',
        'amount',
        'paid_amount',
        'due_date',
        'fine_applied_at',
        'status',
        'finance_transaction_id',
    ];

    protected function casts(): array
    {
        return[
            'year'=>'integer',
            'month'=>'integer',
            'base_amount'=>'decimal:2',
            'share_count'=>'integer',
            'fine_amount'=>'decimal:2',
            'amount'=>'decimal:2',
            'paid_amount'=>'decimal:2',
            'due_date'=>'date',
            'fine_applied_at'=>'date',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            MemberSubscription::class,
            'member_subscription_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            SubscriptionPayment::class,
            'subscription_due_id'
        );
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'finance_transaction_id'
        );
    }

    public function getOutstandingAttribute(): float
    {
        return max(
            round(
                (float)$this->amount-
                (float)$this->paid_amount,
                2
            ),
            0
        );
    }

    public function getPeriodAttribute(): string
    {
        return sprintf(
            '%04d-%02d',
            $this->year,
            $this->month
        );
    }
    
}