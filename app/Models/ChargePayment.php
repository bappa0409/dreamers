<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargePayment extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Charge Payment';
    protected string $activityLogLabelColumn='payment_no';

    protected $fillable=[
        'payment_no',
        'member_charge_id',
        'amount',
        'receive_account_id',
        'payment_date',
        'payment_method',
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
            'payment_date'=>'date',
        ];
    }

    public function charge(): BelongsTo
    {
        return $this->belongsTo(MemberCharge::class,'member_charge_id');
    }

    public function receiveAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class,'receive_account_id');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class,'finance_transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
}