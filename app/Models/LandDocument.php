<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'land_id',
        'document_type',
        'document_number',
        'file_path',
        'file_name',
        'document_date',
        'description',
    ];

    protected $casts = [
        'document_date' => 'date',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }
}