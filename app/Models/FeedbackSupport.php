<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackSupport extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Feedback & Support';
    protected string $activityLogLabelColumn='ticket_no';

    protected $fillable=[
        'ticket_no',
        'member_id',
        'feedback_support_category_id',
        'type',
        'subject',
        'description',
        'priority',
        'status',
        'is_confidential',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'resolution',
        'resolved_by',
        'resolved_at',
        'closed_by',
        'closed_at',
        'created_by'
    ];

    protected $casts=[
        'is_confidential'=>'boolean',
        'assigned_at'=>'datetime',
        'resolved_at'=>'datetime',
        'closed_at'=>'datetime'
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            FeedbackSupportCategory::class,
            'feedback_support_category_id'
        );
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class,'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class,'assigned_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FeedbackSupportAttachment::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(FeedbackSupportUpdate::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(FeedbackSupportStatusHistory::class);
    }
}