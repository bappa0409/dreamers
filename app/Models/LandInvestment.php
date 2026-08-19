<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandInvestment extends Model
{
    use HasFactory;

    protected $fillable=[
        'land_id',
        'member_id',
        'amount',
        'ownership_percentage',
        'investment_date',
        'status',
        'notes'
    ];

    protected $casts=[
        'amount'=>'decimal:2',
        'ownership_percentage'=>'decimal:4',
        'investment_date'=>'date'
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}