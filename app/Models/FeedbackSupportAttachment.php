<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackSupportAttachment extends Model
{
    use SoftDeletes;

    protected $fillable=[
        'feedback_support_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by'
    ];

    public function feedbackSupport(): BelongsTo
    {
        return $this->belongsTo(FeedbackSupport::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class,'uploaded_by');
    }
}