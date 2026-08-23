<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommitteePosition extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Committee Position';
    protected string $activityLogLabelColumn='name';

    protected $fillable=['code','name','description','is_exclusive','is_active','sort_order'];
    protected $casts=['is_exclusive'=>'boolean','is_active'=>'boolean'];

    public function committeeMembers(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(ElectionCandidate::class);
    }
}