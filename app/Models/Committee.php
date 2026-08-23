<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Committee extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Committee';
    protected string $activityLogLabelColumn='name';

    protected $fillable=['name','type','description','is_active','created_by'];
    protected $casts=['is_active'=>'boolean'];

    public function terms(): HasMany
    {
        return $this->hasMany(CommitteeTerm::class);
    }

    public function activeTerm()
    {
        return $this->hasOne(CommitteeTerm::class)->where('status','active');
    }
}