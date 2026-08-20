<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Transaction extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Accounting';
    protected string $activityLogLabelColumn='transaction_no';

    protected $fillable=[
        'transaction_no',
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
    ];

    protected function casts(): array
    {
        return[
            'transaction_date'=>'date',
            'posted_at'=>'datetime',
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
}