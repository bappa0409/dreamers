<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TellerTransaction extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Teller';
    protected string $activityLogLabelColumn='transaction_no';

    protected $fillable=[
        'transaction_no',
        'teller_id',
        'member_id',
        'cash_account_id',
        'counter_account_id',
        'type',
        'amount',
        'purpose',
        'description',
        'transaction_date',
        'status',
        'finance_transaction_id'
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'transaction_date'=>'date'
        ];
    }

    public function teller(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'teller_id'
        );
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'cash_account_id'
        );
    }

    public function counterAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'counter_account_id'
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