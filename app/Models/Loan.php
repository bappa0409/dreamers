<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Loan';
    protected string $activityLogLabelColumn='loan_no';

    protected $fillable=[
        'loan_no',
        'member_id',
        'requested_amount',
        'approved_amount',
        'interest_rate',
        'interest_amount',
        'total_payable',
        'duration_months',
        'request_date',
        'maturity_date',
        'status',
        'purpose',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'disbursement_account_id',
        'disbursement_date',
        'finance_transaction_id',
        'created_by'
    ];

    protected function casts(): array
    {
        return[
            'requested_amount'=>'decimal:2',
            'approved_amount'=>'decimal:2',
            'interest_rate'=>'decimal:4',
            'interest_amount'=>'decimal:2',
            'total_payable'=>'decimal:2',
            'request_date'=>'date',
            'maturity_date'=>'date',
            'disbursement_date'=>'date',
            'approved_at'=>'datetime',
            'rejected_at'=>'datetime'
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class,'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class,'rejected_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function disbursementAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class,'disbursement_account_id');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class,'finance_transaction_id');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function getPaidPrincipalAttribute(): float
    {
        return round((float)$this->repayments()->sum('principal_amount'),2);
    }

    public function getPaidInterestAttribute(): float
    {
        return round((float)$this->repayments()->sum('interest_amount'),2);
    }

    public function getPaidTotalAttribute(): float
    {
        return round((float)$this->repayments()->sum('total_amount'),2);
    }

    public function getOutstandingAttribute(): float
    {
        return max(0,round((float)$this->total_payable-$this->paid_total,2));
    }
}