<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelfareDocument extends Model
{
    use SoftDeletes;

    protected $fillable=[
        'welfare_request_id',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(
            WelfareRequest::class,
            'welfare_request_id'
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}