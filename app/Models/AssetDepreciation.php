<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Asset Depreciation';
    protected string $activityLogLabelColumn='period_key';

    protected $fillable=[
        'asset_id',
        'depreciation_date',
        'amount',
        'book_value_before',
        'book_value_after',
        'period_key',
        'description',
        'finance_transaction_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return[
            'depreciation_date'=>'date',
            'amount'=>'decimal:2',
            'book_value_before'=>'decimal:2',
            'book_value_after'=>'decimal:2',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'finance_transaction_id'
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