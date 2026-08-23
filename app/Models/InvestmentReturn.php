<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentReturn extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Investment';
    protected string $activityLogLabelColumn='id';

    protected $fillable=[
        'investment_id',
        'return_type',
        'receive_account_id',
        'amount',
        'return_date',
        'description',
        'status',
        'finance_transaction_id',
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'return_date'=>'date',
        ];
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(
            Investment::class
        );
    }

    public function receiveAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'receive_account_id'
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