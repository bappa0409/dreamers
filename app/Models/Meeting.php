<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Meeting';
    protected string $activityLogLabelColumn='meeting_no';

    protected $fillable=[
        'meeting_no','title','type','meeting_date','start_time','end_time',
        'venue','description','budget_amount','status','created_by',
        'completed_by','completed_at','minutes','notes'
    ];

    protected function casts(): array
    {
        return[
            'meeting_date'=>'date',
            'budget_amount'=>'decimal:2',
            'completed_at'=>'datetime'
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class,'completed_by');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(MeetingAgenda::class)->orderBy('sort_order');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(MeetingDecision::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(MeetingExpense::class);
    }

    public function postedExpenses(): HasMany
    {
        return $this->expenses()->where('status','posted');
    }
}