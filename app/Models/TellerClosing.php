<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TellerClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'teller_id',
        'closing_date',
        'opening_balance',
        'total_received',
        'total_paid',
        'expected_balance',
        'actual_balance',
        'difference',
        'status',
        'notes',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'opening_balance' => 'decimal:2',
        'total_received' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'actual_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}