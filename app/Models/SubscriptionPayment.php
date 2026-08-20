<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Subscription Payment';
    protected string $activityLogLabelColumn='payment_no';

    protected $fillable=[
        'payment_no',
        'member_id',
        'subscription_due_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'status',
        'paid_at',
        'verified_by',
        'verified_at',
        'verification_note',
        'finance_transaction_id',
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'paid_at'=>'datetime',
            'verified_at'=>'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function due(): BelongsTo
    {
        return $this->belongsTo(
            SubscriptionDue::class,
            'subscription_due_id'
        );
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'finance_transaction_id'
        );
    }
}