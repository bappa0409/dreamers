<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberCharge extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Member Charge';
    protected string $activityLogLabelColumn='charge_no';

    protected $fillable=[
        'charge_no',
        'member_id',
        'income_account_id',
        'amount',
        'paid_amount',
        'charge_date',
        'due_date',
        'charge_type',
        'reference',
        'description',
        'status',
        'finance_transaction_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'paid_amount'=>'decimal:2',
            'charge_date'=>'date',
            'due_date'=>'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function incomeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class,'income_account_id');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class,'finance_transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ChargePayment::class,'member_charge_id');
    }

    public function getOutstandingAttribute(): float
    {
        return max(round((float)$this->amount-(float)$this->paid_amount,2),0);
    }
}