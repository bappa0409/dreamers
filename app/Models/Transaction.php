<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Accounting';
    protected string $activityLogLabelColumn='transaction_no';

    protected $fillable=[
        'transaction_no',
        'idempotency_key',
        'transaction_date',
        'type',
        'source_module',
        'source_id',
        'reference_type',
        'reference_id',
        'description',
        'status',
        'created_by',
        'posted_at',
        'posted_by',
        'cancel_reason',
        'reversal_transaction_id',
        'reversed_by',
        'reversed_at',
    ];

    protected function casts(): array
    {
        return[
            'transaction_date'=>'date',
            'posted_at'=>'datetime',
            'reversed_at'=>'datetime',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TransactionEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class,'posted_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'reversed_by');
    }

    public function reversalTransaction(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'reversal_transaction_id'
        );
    }

    public function reversedOriginal(): HasOne
    {
        return $this->hasOne(
            self::class,
            'reversal_transaction_id'
        );
    }

    public function getIsReversedAttribute(): bool
    {
        return $this->reversed_at!==null;
    }
}