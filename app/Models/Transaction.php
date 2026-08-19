<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Transaction';
    protected string $activityLogLabelColumn='transaction_no';

    protected $fillable=[
        'transaction_no',
        'transaction_date',
        'type',
        'reference_type',
        'reference_id',
        'created_by',
        'description',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'transaction_date'=>'date'
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
}