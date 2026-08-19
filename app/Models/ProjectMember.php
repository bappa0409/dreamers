<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Project Member';
    protected string $activityLogLabelColumn='id';

    protected $fillable=[
        'project_id','member_id','contribution','role','joined_date','status'
    ];

    protected function casts(): array
    {
        return [
            'contribution'=>'decimal:2',
            'joined_date'=>'date'
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}