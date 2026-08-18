<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'amount',
        'return_date',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'return_date' => 'date',
    ];

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }
}