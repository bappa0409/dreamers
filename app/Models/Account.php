<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Account extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Finance';
    protected string $activityLogLabelColumn='name';

    protected $fillable=[
        'parent_id',
        'code',
        'name',
        'type',
        'sub_type',
        'opening_balance',
        'is_system',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return[
            'opening_balance'=>'decimal:2',
            'is_system'=>'boolean',
            'is_active'=>'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class,'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class,'parent_id')->orderBy('code');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TransactionEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active',true);
    }

    public function scopePosting($query)
    {
        return $query->whereDoesntHave('children');
    }

    public function getCurrentBalanceAttribute(): float
    {
        $debit=(float)$this->entries()->sum('debit');
        $credit=(float)$this->entries()->sum('credit');
        $opening=(float)$this->opening_balance;

        return in_array($this->type,['asset','expense'],true)
            ?$opening+$debit-$credit
            :$opening+$credit-$debit;
    }
}