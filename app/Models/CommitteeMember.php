<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeMember extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Committee Member';

    protected $fillable=[
        'committee_term_id','committee_position_id','member_id',
        'appointment_method','start_date','end_date','status','notes','created_by'
    ];

    protected $casts=['start_date'=>'date','end_date'=>'date'];

    public function term(): BelongsTo
    {
        return $this->belongsTo(CommitteeTerm::class,'committee_term_id');
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