<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Land extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Land';
    protected string $activityLogLabelColumn='land_code';

    protected $fillable=[
        'land_code','title','description','district','upazila','mouza',
        'khatian_no','dag_no','land_area','area_unit','purchase_price',
        'current_value','sale_price','selling_expense','purchase_date',
        'sale_date','seller_name','seller_phone','buyer_name','buyer_phone',
        'status','notes'
    ];

    protected function casts(): array
    {
        return [
            'land_area'=>'decimal:4',
            'purchase_price'=>'decimal:2',
            'current_value'=>'decimal:2',
            'sale_price'=>'decimal:2',
            'selling_expense'=>'decimal:2',
            'purchase_date'=>'date',
            'sale_date'=>'date'
        ];
    }

    protected $appends=[
        'net_sale_amount',
        'profit_loss',
        'profit_percentage'
    ];

    public function investments(): HasMany
    {
        return $this->hasMany(LandInvestment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LandDocument::class);
    }

    public function getNetSaleAmountAttribute(): float
    {
        if($this->sale_price===null){
            return 0;
        }

        return max(
            (float)$this->sale_price-(float)$this->selling_expense,
            0
        );
    }

    public function getProfitLossAttribute(): float
    {
        if($this->sale_price===null){
            return 0;
        }

        return $this->net_sale_amount-(float)$this->purchase_price;
    }

    public function getProfitPercentageAttribute(): float
    {
        $purchase=(float)$this->purchase_price;

        if($purchase<=0||$this->sale_price===null){
            return 0;
        }

        return round(
            ($this->profit_loss/$purchase)*100,
            2
        );
    }
}