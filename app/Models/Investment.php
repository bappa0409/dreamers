<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Investment';
    protected string $activityLogLabelColumn='investment_no';

    protected $fillable=[
        'investment_no','member_id','title','description','amount',
        'expected_return','investment_date','maturity_date','status'
    ];

    protected function casts(): array
    {
        return [
            'amount'=>'decimal:2',
            'expected_return'=>'decimal:2',
            'investment_date'=>'date',
            'maturity_date'=>'date'
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(InvestmentReturn::class);
    }

    public function paidReturns(): HasMany
    {
        return $this->returns()->where('status','paid');
    }
}