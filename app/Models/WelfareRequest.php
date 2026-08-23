<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WelfareRequest extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Welfare';
    protected string $activityLogLabelColumn='request_no';

    protected $fillable=[
        'request_no',
        'welfare_fund_id',
        'member_id',
        'assistance_type',
        'requested_amount',
        'approved_amount',
        'reason',
        'notes',
        'request_date',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'payment_account_id',
        'disbursement_date',
        'finance_transaction_id',
        'reversal_transaction_id',
        'completed_at',
        'created_by'
    ];

    protected $casts=[
        'requested_amount'=>'decimal:2',
        'approved_amount'=>'decimal:2',
        'request_date'=>'date',
        'disbursement_date'=>'date',
        'reviewed_at'=>'datetime',
        'approved_at'=>'datetime',
        'rejected_at'=>'datetime',
        'completed_at'=>'datetime'
    ];

    public function fund(): BelongsTo
    {
        return $this->belongsTo(
            WelfareFund::class,
            'welfare_fund_id'
        );
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            WelfareDocument::class
        );
    }

    public function histories(): HasMany
    {
        return $this->hasMany(
            WelfareRequestHistory::class
        );
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'payment_account_id'
        );
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'finance_transaction_id'
        );
    }

    public function reversalTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'reversal_transaction_id'
        );
    }
}