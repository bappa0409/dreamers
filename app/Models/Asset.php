<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Asset';
    protected string $activityLogLabelColumn='asset_code';

    protected $fillable=[
        'asset_code',
        'name',
        'category',
        'asset_account_id',
        'payment_account_id',
        'purchase_cost',
        'purchase_date',
        'vendor',
        'reference',
        'location',
        'serial_no',
        'attachment',
        'useful_life_months',
        'salvage_value',
        'accumulated_depreciation',
        'status',
        'disposal_date',
        'disposal_amount',
        'disposal_note',
        'finance_transaction_id',
        'disposal_transaction_id',
        'created_by',
        'description',
    ];

    protected function casts(): array
    {
        return[
            'purchase_cost'=>'decimal:2',
            'purchase_date'=>'date',
            'salvage_value'=>'decimal:2',
            'accumulated_depreciation'=>'decimal:2',
            'disposal_date'=>'date',
            'disposal_amount'=>'decimal:2',
            'useful_life_months'=>'integer',
        ];
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class,'asset_account_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class,'payment_account_id');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class,'finance_transaction_id');
    }

    public function disposalTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class,'disposal_transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function getBookValueAttribute(): float
    {
        return max(
            round(
                (float)$this->purchase_cost-
                (float)$this->accumulated_depreciation,
                2
            ),
            0
        );
    }
}