<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'approval_status',
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
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        )->orderBy('code');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(
            TransactionEntry::class
        );
    }

    public function postedEntries(): HasMany
    {
        return $this->hasMany(
            TransactionEntry::class
        )->whereHas(
            'transaction',
            fn($query)=>$query->where(
                'status',
                'posted'
            )
        );
    }

    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }

    public function scopePosting($query)
    {
        return $query->whereDoesntHave(
            'children'
        );
    }

    public function calculateBalance(
        ?float $debit=null,
        ?float $credit=null
    ): float{
        if($debit===null){
            $debit=array_key_exists(
                'total_debit',
                $this->attributes
            )
                ?(float)$this->attributes['total_debit']
                :(float)$this->postedEntries()
                    ->sum('debit');
        }

        if($credit===null){
            $credit=array_key_exists(
                'total_credit',
                $this->attributes
            )
                ?(float)$this->attributes['total_credit']
                :(float)$this->postedEntries()
                    ->sum('credit');
        }

        $opening=(float)$this->opening_balance;

        $balance=in_array(
            $this->type,
            ['asset','expense'],
            true
        )
            ?$opening+$debit-$credit
            :$opening+$credit-$debit;

        return round(
            $balance,
            2
        );
    }

    public function getCurrentBalanceAttribute(): float
    {
        return $this->calculateBalance();
    }
}