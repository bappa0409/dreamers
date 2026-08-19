<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationCampaign extends Model
{
    protected $fillable=[
        'title','message','type','audience_type','audience_data',
        'action_url','status','recipients_count','sent_by',
        'sent_at','error_message'
    ];

    protected function casts(): array
    {
        return [
            'audience_data'=>'array',
            'sent_at'=>'datetime'
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class,'sent_by');
    }
}