<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandValuation extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Land Valuation';
    protected string $activityLogLabelColumn='id';

    protected $fillable=[
        'land_id',
        'valuation_date',
        'previous_value',
        'current_value',
        'valued_by',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return[
            'valuation_date'=>'date',
            'previous_value'=>'decimal:2',
            'current_value'=>'decimal:2',
        ];
    }

    public function land(): BelongsTo
    {
        return $this->belongsTo(
            Land::class
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