<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailCampaign extends Model
{
    use LogsActivity;

    protected string $activityLogModule = 'Mail Campaign';
    protected string $activityLogLabelColumn = 'subject';

    /**
     * The database uses `content` and `total_recipients`.
     * `body` and `recipients_count` are kept as API/UI aliases so the
     * existing Blade page can continue to use its current payload shape.
     */
    protected $fillable = [
        'subject',
        'content',
        'body',
        'status',
        'created_by',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'recipients_count',
        'sent_count',
        'failed_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    protected $appends = [
        'body',
        'recipients_count',
    ];

    public function getBodyAttribute(): ?string
    {
        return $this->attributes['content'] ?? null;
    }

    public function setBodyAttribute(?string $value): void
    {
        $this->attributes['content'] = $value;
    }

    public function getRecipientsCountAttribute(): int
    {
        return (int) ($this->attributes['total_recipients'] ?? 0);
    }

    public function setRecipientsCountAttribute(int $value): void
    {
        $this->attributes['total_recipients'] = max(0, $value);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MailRecipient::class);
    }
}
