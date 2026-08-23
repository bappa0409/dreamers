<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Tour';
    protected string $activityLogLabelColumn='tour_no';

    protected $fillable=[
        'tour_no','title','destination','description','start_date','end_date',
        'budget_amount','status','created_by','approved_by','approved_at','notes'
    ];

    protected function casts(): array
    {
        return[
            'start_date'=>'date',
            'end_date'=>'date',
            'budget_amount'=>'decimal:2',
            'approved_at'=>'datetime'
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class,'approved_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TourParticipant::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(TourExpense::class);
    }

    public function postedExpenses(): HasMany
    {
        return $this->expenses()->where('status','posted');
    }
}