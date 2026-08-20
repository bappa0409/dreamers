<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    protected $fillable=[
        'module',
        'action',
        'is_active',
    ];

    protected $casts=[
        'is_active'=>'boolean',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(
            ApprovalWorkflowStep::class
        )->orderBy('step_no');
    }
}