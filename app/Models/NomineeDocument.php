<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NomineeDocument extends Model
{
    use SoftDeletes;

    protected $fillable=[
        'member_nominee_id',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by'
    ];

    protected $appends=[
        'file_url'
    ];

    public function nominee(): BelongsTo
    {
        return $this->belongsTo(
            MemberNominee::class,
            'member_nominee_id'
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path
            ?Storage::disk('public')->url($this->file_path)
            :null;
    }
}