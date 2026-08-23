<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackSupportStatusHistory extends Model
{
    protected $fillable=[
        'feedback_support_id',
        'from_status',
        'to_status',
        'note',
        'changed_by'
    ];

    public function feedbackSupport(): BelongsTo
    {
        return $this->belongsTo(FeedbackSupport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'changed_by');
    }
}