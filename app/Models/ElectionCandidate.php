<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionCandidate extends Model
{
    protected $fillable=[
        'election_id','committee_position_id','member_id','votes','status','notes'
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(CommitteePosition::class,'committee_position_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}