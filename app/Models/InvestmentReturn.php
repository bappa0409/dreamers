<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentReturn extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Investment Return';
    protected string $activityLogLabelColumn='id';

    protected $fillable=[
        'investment_id','amount','return_date','description','status'
    ];

    protected function casts(): array
    {
        return [
            'amount'=>'decimal:2',
            'return_date'=>'date'
        ];
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }
}