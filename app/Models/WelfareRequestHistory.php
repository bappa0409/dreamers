<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelfareRequestHistory extends Model
{
    protected $fillable=[
        'welfare_request_id',
        'from_status',
        'to_status',
        'note',
        'changed_by'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(
            WelfareRequest::class,
            'welfare_request_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'changed_by'
        );
    }
}