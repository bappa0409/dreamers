<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Election extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Election';
    protected string $activityLogLabelColumn='title';

    protected $fillable=[
        'committee_term_id','title','election_date','status','notes','created_by'
    ];

    protected $casts=['election_date'=>'date'];

    public function term(): BelongsTo
    {
        return $this->belongsTo(CommitteeTerm::class,'committee_term_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(ElectionCandidate::class);
    }
}