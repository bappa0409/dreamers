<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Land extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'purchase_date',
        'seller_name',
        'seller_phone',
        'status',
        'notes',
    ];

    protected $casts = [
        'land_area' => 'decimal:4',
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    public function investments(): HasMany
    {
        return $this->hasMany(LandInvestment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LandDocument::class);
    }
}