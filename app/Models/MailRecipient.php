<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailRecipient extends Model
{
    protected $fillable=[
        'mail_campaign_id',
        'user_id',
        'name',
        'email',
        'status',
        'sent_at',
        'error_message'
    ];

    protected $casts=[
        'sent_at'=>'datetime'
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            MailCampaign::class,
            'mail_campaign_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}