<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Notice';
    protected string $activityLogLabelColumn='title';

    protected $fillable=[
        'title','content','type','priority','is_published',
        'publish_at','expires_at','attachment','created_by'
    ];

    protected function casts(): array
    {
        return [
            'is_published'=>'boolean',
            'publish_at'=>'datetime',
            'expires_at'=>'datetime'
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function scopeVisible($query)
    {
        return $query
            ->where('is_published',true)
            ->where(function($q){
                $q->whereNull('publish_at')
                    ->orWhere('publish_at','<=',now());
            })
            ->where(function($q){
                $q->whereNull('expires_at')
                    ->orWhere('expires_at','>=',now());
            });
    }
}