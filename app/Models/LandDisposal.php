<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandDisposal extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Land Disposal';
    protected string $activityLogLabelColumn='id';

    protected $fillable=[
        'land_id',
        'sale_date',
        'sale_price',
        'selling_expense',
        'net_sale_amount',
        'book_value',
        'gain_loss',
        'receive_account_id',
        'buyer_name',
        'buyer_phone',
        'reference_no',
        'notes',
        'finance_transaction_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return[
            'sale_date'=>'date',
            'sale_price'=>'decimal:2',
            'selling_expense'=>'decimal:2',
            'net_sale_amount'=>'decimal:2',
            'book_value'=>'decimal:2',
            'gain_loss'=>'decimal:2',
        ];
    }

    public function land(): BelongsTo
    {
        return $this->belongsTo(
            Land::class
        );
    }

    public function receiveAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'receive_account_id'
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

    public function getProfitPercentageAttribute(): float
    {
        $bookValue=(float)$this->book_value;

        if($bookValue<=0){
            return 0;
        }

        return round(
            ((float)$this->gain_loss/$bookValue)*100,
            2
        );
    }
}