<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function nominee(): BelongsTo
    {
        return $this->belongsTo(
            MemberNominee::class,
            'member_nominee_id'
        )->withTrashed();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}