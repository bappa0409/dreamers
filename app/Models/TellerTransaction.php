<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TellerTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_no',
        'teller_id',
        'member_id',
        'type',
        'amount',
        'purpose',
        'description',
        'transaction_date',
        'status',
        'finance_transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function financeTransaction()
    {
        return $this->belongsTo(Transaction::class, 'finance_transaction_id');
    }
}