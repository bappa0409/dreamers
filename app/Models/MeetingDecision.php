<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingDecision extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Meeting Decision';
    protected string $activityLogLabelColumn='decision_no';

    protected $fillable=[
        'meeting_id','meeting_agenda_id','decision_no','title','decision',
        'result','votes_for','votes_against','votes_abstain',
        'responsible_user_id','due_date','status','completed_at',
        'completion_notes'
    ];

    protected function casts(): array
    {
        return[
            'due_date'=>'date',
            'completed_at'=>'datetime',
            'votes_for'=>'integer',
            'votes_against'=>'integer',
            'votes_abstain'=>'integer'
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(MeetingAgenda::class,'meeting_agenda_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class,'responsible_user_id');
    }
}