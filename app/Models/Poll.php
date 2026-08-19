<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Poll';
    protected string $activityLogLabelColumn='title';

    protected $fillable=[
        'title','description','start_at','end_at','is_active','created_by'
    ];

    protected function casts(): array
    {
        return [
            'start_at'=>'datetime',
            'end_at'=>'datetime',
            'is_active'=>'boolean'
        ];
    }

    protected $appends=[
        'state'
    ];

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function getStateAttribute(): string
    {
        if(!$this->is_active){
            return 'inactive';
        }

        if(now()->lt($this->start_at)){
            return 'upcoming';
        }

        if(now()->gt($this->end_at)){
            return 'ended';
        }

        return 'active';
    }
}