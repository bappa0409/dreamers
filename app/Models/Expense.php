<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Expense';
    protected string $activityLogLabelColumn='expense_no';

    protected $fillable=[
        'expense_no',
        'expense_account_id',
        'payment_account_id',
        'amount',
        'expense_date',
        'payee',
        'reference',
        'description',
        'attachment',
        'finance_transaction_id',
        'created_by',
        'status',
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'expense_date'=>'date',
        ];
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class,'expense_account_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class,'payment_account_id');
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