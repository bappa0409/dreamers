<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelfareFundAllocation extends Model
{
    protected $fillable=[
        'welfare_fund_id',
        'amount',
        'allocation_date',
        'source_type',
        'source_reference',
        'description',
        'source_transaction_id',
        'created_by'
    ];

    protected $casts=[
        'amount'=>'decimal:2',
        'allocation_date'=>'date'
    ];

    public function fund(): BelongsTo
    {
        return $this->belongsTo(
            WelfareFund::class,
            'welfare_fund_id'
        );
    }

    public function sourceTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'source_transaction_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}