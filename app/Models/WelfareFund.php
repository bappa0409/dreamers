<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WelfareFund extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Welfare Fund';
    protected string $activityLogLabelColumn='name';

    protected $fillable=[
        'code',
        'name',
        'description',
        'expense_account_id',
        'start_date',
        'end_date',
        'is_active',
        'created_by'
    ];

    protected $casts=[
        'start_date'=>'date',
        'end_date'=>'date',
        'is_active'=>'boolean'
    ];

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'expense_account_id'
        );
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(
            WelfareFundAllocation::class
        );
    }

    public function requests(): HasMany
    {
        return $this->hasMany(
            WelfareRequest::class
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