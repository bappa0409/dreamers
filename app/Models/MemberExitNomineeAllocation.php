<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberExitNomineeAllocation extends Model
{
    protected $fillable=[
        'member_exit_id',
        'member_nominee_id',
        'allocation_percentage',
        'amount',
    ];

    protected $casts=[
        'allocation_percentage'=>'decimal:2',
        'amount'=>'decimal:2',
    ];

    public function exit(): BelongsTo
    {
        return $this->belongsTo(MemberExit::class);
    }

    public function nominee(): BelongsTo
    {
        return $this->belongsTo(
            MemberNominee::class,
            'member_nominee_id'
        );
    }
}