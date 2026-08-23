<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Land extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Land';
    protected string $activityLogLabelColumn='land_code';

    protected $fillable=[
        'land_code',
        'title',
        'description',
        'district',
        'upazila',
        'mouza',
        'khatian_no',
        'dag_no',
        'land_area',
        'area_unit',
        'purchase_price',
        'current_value',
        'purchase_date',
        'seller_name',
        'seller_phone',
        'deed_no',
        'registration_no',
        'payment_account_id',
        'finance_transaction_id',
        'created_by',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return[
            'land_area'=>'decimal:4',
            'purchase_price'=>'decimal:2',
            'current_value'=>'decimal:2',
            'purchase_date'=>'date',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            LandDocument::class
        );
    }

    public function valuations(): HasMany
    {
        return $this->hasMany(
            LandValuation::class
        );
    }

    public function disposal(): HasOne
    {
        return $this->hasOne(
            LandDisposal::class
        );
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn(
            'status',
            ['sold','cancelled']
        );
    }
}