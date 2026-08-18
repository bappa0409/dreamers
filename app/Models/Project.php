<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'name',
        'description',
        'location',
        'budget',
        'actual_cost',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'progress',
        'status',
        'notes',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }
}