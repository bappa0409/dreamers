<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberExitItem extends Model
{
    protected $fillable=[
        'member_exit_id',
        'category',
        'direction',
        'reference_type',
        'reference_id',
        'description',
        'amount',
        'is_blocking',
        'status',
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'is_blocking'=>'boolean',
        ];
    }

    public function exit(): BelongsTo
    {
        return $this->belongsTo(
            MemberExit::class,
            'member_exit_id'
        );
    }
}