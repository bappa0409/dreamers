<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourParticipant extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Tour Participant';

    protected $fillable=['tour_id','member_id','status','notes'];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}