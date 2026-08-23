<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingAgenda extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Meeting Agenda';

    protected $fillable=[
        'meeting_id','sort_order','title','description','status'
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(MeetingDecision::class);
    }
}