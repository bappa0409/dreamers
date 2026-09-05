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
        'father_or_husband_name',
        'mother_name',
        'phone',
        'alternate_phone',
        'date_of_birth',
        'gender',
        'nid_or_birth_reg_no',
        'nid_document',
        'address',
        'permanent_address',
        'profession',
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
        'nid_document_url',
    ];

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo
            ? asset('storage/' . $this->profile_photo)
            : null;
    }

    public function getNidDocumentUrlAttribute(): ?string
    {
        return $this->nid_document
            ? asset('storage/' . $this->nid_document)
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


    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function nominees(): HasMany
    {
        return $this->hasMany(MemberNominee::class);
    }

    public function activeNominees(): HasMany
    {
        return $this->nominees()
            ->where('is_active', true);
    }

    public function exits(): HasMany
    {
        return $this->hasMany(MemberExit::class);
    }

    public function currentExit()
    {
        return $this->hasOne(MemberExit::class)
            ->whereNotIn('status', [
                'closed',
                'rejected',
                'cancelled',
            ])
            ->latestOfMany();
    }

    public function welfareRequests(): HasMany
    {
        return $this->hasMany(WelfareRequest::class);
    }

    public function feedbackSupports(): HasMany
    {
        return $this->hasMany(FeedbackSupport::class);
    }

    public function activeShares(): HasMany
    {
        return $this->hasMany(MemberShare::class)
            ->where('status', 'active');
    }
}