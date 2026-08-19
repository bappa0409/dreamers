<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    protected $fillable = [
        'filename',
        'disk',
        'path',
        'size',
        'type',
        'status',
        'failure_reason',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'size'         => 'integer',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}