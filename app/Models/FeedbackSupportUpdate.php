<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackSupportUpdate extends Model
{
    protected $fillable=[
        'feedback_support_id',
        'type',
        'message',
        'created_by'
    ];

    public function feedbackSupport(): BelongsTo
    {
        return $this->belongsTo(FeedbackSupport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
}