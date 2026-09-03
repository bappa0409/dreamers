<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Contact Message';
    protected string $activityLogLabelColumn='subject';

    protected $fillable=[
        'name','email','subject','message',
        'is_read','read_at','read_by',
        'ip_address','user_agent'
    ];

    protected function casts(): array
    {
        return [
            'is_read'=>'boolean',
            'read_at'=>'datetime'
        ];
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(User::class,'read_by');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read',false);
    }
}
