<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailCampaign extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Mail Campaign';
    protected string $activityLogLabelColumn='subject';

    protected $fillable=[
        'subject',
        'body',
        'status',
        'created_by',
        'sent_at',
        'recipients_count',
        'sent_count',
        'failed_count'
    ];

    protected $casts=[
        'sent_at'=>'datetime',
        'recipients_count'=>'integer',
        'sent_count'=>'integer',
        'failed_count'=>'integer'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MailRecipient::class);
    }
}