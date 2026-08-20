<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Member extends Model
{
    use HasFactory, LogsActivity;

    protected string $activityLogModule = 'Member';
    protected string $activityLogLabelColumn = 'member_code';

    protected $fillable = [
        'user_id',
        'member_code',
        'phone',
        'alternate_phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'district',
        'joining_date',
        'status',
        'profile_photo',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo
            ? asset('storage/' . $this->profile_photo)
            : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function landInvestments(): HasMany
    {
        return $this->hasMany(LandInvestment::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function tellerTransactions()
    {
        return $this->hasMany(TellerTransaction::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(MemberCharge::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(MemberShare::class);
    }

    public function activeShares(): HasMany
    {
        return $this->hasMany(MemberShare::class)
            ->where('status', 'active');
    }
}
