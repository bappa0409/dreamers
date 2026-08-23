<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberExit extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Member Exit';
    protected string $activityLogLabelColumn='exit_no';

    protected $fillable=[
        'exit_no',
        'member_id',
        'exit_type',
        'request_date',
        'proposed_exit_date',
        'reason',
        'status',
        'member_initiated',
        'subscription_due',
        'charge_due',
        'loan_due',
        'total_liabilities',
        'share_refund',
        'net_settlement_amount',
        'blocking_items_count',
        'review_note',
        'initiated_by',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'payout_account_id',
        'settlement_transaction_id',
        'settled_by',
        'settled_at',
        'closed_by',
        'closed_at',
    ];

    protected function casts(): array
    {
        return[
            'request_date'=>'date',
            'proposed_exit_date'=>'date',
            'member_initiated'=>'boolean',

            'subscription_due'=>'decimal:2',
            'charge_due'=>'decimal:2',
            'loan_due'=>'decimal:2',
            'total_liabilities'=>'decimal:2',
            'share_refund'=>'decimal:2',
            'net_settlement_amount'=>'decimal:2',

            'reviewed_at'=>'datetime',
            'approved_at'=>'datetime',
            'rejected_at'=>'datetime',
            'settled_at'=>'datetime',
            'closed_at'=>'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MemberExitItem::class);
    }

    public function nomineeAllocations(): HasMany
    {
        return $this->hasMany(
            MemberExitNomineeAllocation::class
        );
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'initiated_by'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'payout_account_id'
        );
    }

    public function settlementTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'settlement_transaction_id'
        );
    }
}