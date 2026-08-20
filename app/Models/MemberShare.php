<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberShare extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Member Share';
    protected string $activityLogLabelColumn='share_no';

    protected $fillable=[
        'member_id',
        'share_no',
        'purchase_amount',
        'acquired_date',
        'payment_method',
        'transaction_reference',
        'status',
        'created_by',
        'verified_by',
        'verified_at',
        'verification_note',
        'finance_transaction_id',
        'notes',
    ];

    protected function casts(): array
    {
        return[
            'purchase_amount'=>'decimal:2',
            'acquired_date'=>'date',
            'verified_at'=>'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
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

    public function scopeActive($query)
    {
        return $query->where(
            'status',
            'active'
        );
    }

    public function scopePending($query)
    {
        return $query->where(
            'status',
            'pending'
        );
    }
}