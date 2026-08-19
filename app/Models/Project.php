<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Project';
    protected string $activityLogLabelColumn='project_code';

    protected $fillable=[
        'project_code','name','description','location','budget','actual_cost',
        'start_date','expected_end_date','actual_end_date','progress','status','notes'
    ];

    protected function casts(): array
    {
        return [
            'budget'=>'decimal:2',
            'actual_cost'=>'decimal:2',
            'start_date'=>'date',
            'expected_end_date'=>'date',
            'actual_end_date'=>'date',
            'progress'=>'integer'
        ];
    }

    protected $appends=[
        'remaining_budget',
        'budget_usage_percentage'
    ];

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function getRemainingBudgetAttribute(): float
    {
        return (float)$this->budget-(float)$this->actual_cost;
    }

    public function getBudgetUsagePercentageAttribute(): float
    {
        $budget=(float)$this->budget;

        if($budget<=0){
            return 0;
        }

        return round(
            ((float)$this->actual_cost/$budget)*100,
            2
        );
    }
}