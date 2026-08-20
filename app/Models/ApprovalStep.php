<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    protected $fillable=[
        'approval_request_id',
        'step_no',
        'approver_user_id',
        'status',
        'remarks',
        'acted_at',
    ];

    protected function casts(): array
    {
        return[
            'step_no'=>'integer',
            'acted_at'=>'datetime',
        ];
    }

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(
            ApprovalRequest::class,
            'approval_request_id'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approver_user_id'
        );
    }
}