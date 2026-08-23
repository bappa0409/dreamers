<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LandDocument extends Model
{
    use HasFactory,LogsActivity;

    protected string $activityLogModule='Land Document';
    protected string $activityLogLabelColumn='document_number';

    protected $fillable=[
        'land_id',
        'document_type',
        'document_number',
        'file_path',
        'file_name',
        'document_date',
        'description',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return[
            'document_date'=>'date',
        ];
    }

    protected $appends=[
        'file_url',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(
            Land::class
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
        if(!$this->file_path){
            return null;
        }

        return Storage::disk('public')->url(
            $this->file_path
        );
    }
}