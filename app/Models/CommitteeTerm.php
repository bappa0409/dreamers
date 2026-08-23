<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommitteeTerm extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Committee Term';
    protected string $activityLogLabelColumn='name';

    protected $fillable=[
        'committee_id','name','start_date','end_date','status','notes','created_by'
    ];

    protected $casts=['start_date'=>'date','end_date'=>'date'];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->members()->where('status','active');
    }

    public function elections(): HasMany
    {
        return $this->hasMany(Election::class);
    }
}