<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingExpense extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Meeting Expense';
    protected string $activityLogLabelColumn='expense_no';

    protected $fillable=[
        'meeting_id','expense_no','category','expense_account_id',
        'payment_account_id','amount','expense_date','payee','reference_no',
        'description','status','finance_transaction_id','created_by'
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'expense_date'=>'date'
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
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