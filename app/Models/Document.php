<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Document';
    protected string $activityLogLabelColumn='title';

    protected $fillable=[
        'title',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'extension',
        'size',
        'category',
        'description',
        'visibility',
        'is_active',
        'uploaded_by'
    ];

    protected function casts(): array
    {
        return [
            'size'=>'integer',
            'is_active'=>'boolean'
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class,'uploaded_by');
    }

    public function scopeVisibleTo($query,User $user)
    {
        if($user->isSystemAnalyst()||$user->hasPermission('Document.manage')){
            return $query;
        }

        return $query
            ->where('is_active',true)
            ->whereIn('visibility',[
                'public',
                'members'
            ]);
    }
}