<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    use HasFactory, LogsActivity;

    protected string $activityLogModule='Loan';

    protected $fillable=[
        'loan_id',
        'receive_account_id',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'total_amount',
        'repayment_date',
        'notes',
        'finance_transaction_id',
        'received_by'
    ];

    protected function casts(): array
    {
        return[
            'principal_amount'=>'decimal:2',
            'interest_amount'=>'decimal:2',
            'penalty_amount'=>'decimal:2',
            'total_amount'=>'decimal:2',
            'repayment_date'=>'date'
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function receiveAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class,'receive_account_id');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class,'finance_transaction_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class,'received_by');
    }
}