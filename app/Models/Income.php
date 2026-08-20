<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Income';
    protected string $activityLogLabelColumn='income_no';

    protected $fillable=[
        'income_no',
        'member_id',
        'income_account_id',
        'receive_account_id',
        'amount',
        'income_date',
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
            'income_date'=>'date',
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