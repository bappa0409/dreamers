<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    use HasFactory, LogsActivity;

    protected string $activityLogModule='Investment';
    protected string $activityLogLabelColumn='investment_no';

    protected $fillable=[
        'investment_no',
        'payment_account_id',
        'title',
        'description',
        'amount',
        'expected_return',
        'investment_date',
        'maturity_date',
        'status',
        'finance_transaction_id',
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'expected_return'=>'decimal:2',
            'investment_date'=>'date',
            'maturity_date'=>'date',
        ];
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'payment_account_id'
        );
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'finance_transaction_id'
        );
    }

    public function returns(): HasMany
    {
        return $this->hasMany(
            InvestmentReturn::class
        );
    }

    public function getPrincipalReturnedAttribute(): float
    {
        if(array_key_exists(
            'principal_return_total',
            $this->attributes
        )){
            return (float)$this->attributes[
                'principal_return_total'
            ];
        }

        return (float)$this->returns()
            ->where('status','paid')
            ->where('return_type','principal')
            ->sum('amount');
    }

    public function getRemainingPrincipalAttribute(): float
    {
        return max(
            round(
                (float)$this->amount-
                $this->principal_returned,
                2
            ),
            0
        );
    }

    public function getIncomeReceivedAttribute(): float
    {
        if(array_key_exists(
            'paid_return_total',
            $this->attributes
        )){
            return (float)$this->attributes[
                'paid_return_total'
            ];
        }

        return (float)$this->returns()
            ->where('status','paid')
            ->where('return_type','income')
            ->sum('amount');
    }
}