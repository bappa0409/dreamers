<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MemberNominee extends Model
{
    use LogsActivity,SoftDeletes;

    protected string $activityLogModule='Nominee';
    protected string $activityLogLabelColumn='name';

    protected $fillable=[
        'member_id',
        'name',
        'relationship',
        'father_or_husband_name',
        'mother_name',
        'phone',
        'identity_type',
        'identity_number',
        'date_of_birth',
        'gender',
        'profession',
        'address',
        'permanent_address',
        'photo',
        'allocation_percentage',
        'priority',
        'is_active',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_note',
        'rejection_reason',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts=[
        'date_of_birth'=>'date',
        'allocation_percentage'=>'decimal:2',
        'is_active'=>'boolean',
        'verified_at'=>'datetime'
    ];

    protected $appends=[
        'photo_url'
    ];

    /**
     * Public URL for the nominee's photo (stored on the "public" disk,
     * same convention as Member::profile_photo).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if(!$this->photo){
            return null;
        }

        return Storage::disk('public')->url($this->photo);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            NomineeDocument::class
        );
    }

    public function activeDocuments(): HasMany
    {
        return $this->documents()
            ->whereNull('deleted_at');
    }
}