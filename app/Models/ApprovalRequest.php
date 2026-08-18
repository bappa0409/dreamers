<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'module',
        'action',
        'status',
        'requested_by',
        'request_note',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason'
    ];

    protected function casts(): array
    {
        return [
            'approved_at'=>'datetime',
            'rejected_at'=>'datetime',
            'cancelled_at'=>'datetime',
        ];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class,'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class,'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class,'rejected_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class,'cancelled_by');
    }
}