<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberShare;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MemberService
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected SubscriptionService $subscriptionService,
        protected NumberSequenceService $numberSequenceService
    ) {}

    public function createMember(array $data): Member
    {
        $storedProfilePhoto = null;

        try {
            if (
                isset($data['profile_photo']) &&
                $data['profile_photo'] instanceof UploadedFile
            ) {
                $storedProfilePhoto = $data['profile_photo']->store(
                    'members/profile-photos',
                    'public'
                );

                $data['profile_photo'] = $storedProfilePhoto;
            }

            return DB::transaction(function () use ($data) {
                $memberRole = Role::where('name', 'member')->first();

                if (!$memberRole) {
                    throw ValidationException::withMessages([
                        'role' => [
                            'Default Member role is not configured.'
                        ],
                    ]);
                }

                $autoActivate = $this->autoActivateMember();

                $defaultLanguage = setting(
                    'default_language',
                    'en'
                );

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'mobile' => $data['mobile'] ?? null,
                    'password' => Hash::make($data['email']),
                    'password_setup_token' => null,
                    'password_setup_expires_at' => null,
                    'language' => $data['language']
                        ?? $defaultLanguage,
                    'is_active' => $autoActivate,
                    'role_id' => $memberRole->id,
                ]);

                $user->roles()->syncWithoutDetaching([
                    $memberRole->id
                ]);

                $user->forgetAuthorizationCache();

                $member = Member::create([
                    'user_id' => $user->id,
                    'member_code' => $this->generateMemberCode(),
                    'phone' => $data['phone']
                        ?? $data['mobile']
                        ?? null,
                    'alternate_phone' => $data['alternate_phone'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'district' => $data['district'] ?? null,
                    'joining_date' => $autoActivate
                        ? now()->toDateString()
                        : null,
                    'status' => $autoActivate
                        ? 'active'
                        : 'pending',
                    'profile_photo' => $data['profile_photo'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                if ($this->shareEnabled()) {
                    $this->createInitialShare(
                        $member,
                        $autoActivate,
                        $user->id
                    );
                }

                $this->forgetMemberCaches();

                return $member->load([
                    'user.roles',
                    'shares.creator',
                ]);
            });
        } catch (\Throwable $e) {
            if (
                $storedProfilePhoto &&
                Storage::disk('public')->exists(
                    $storedProfilePhoto
                )
            ) {
                Storage::disk('public')->delete(
                    $storedProfilePhoto
                );
            }

            throw $e;
        }
    }

    public function activateInitialShare(
        Member $member,
        ?int $userId = null
    ): ?MemberShare {
        if (!$this->shareEnabled()) {
            return null;
        }

        return DB::transaction(function () use (
            $member,
            $userId
        ) {
            $member = Member::query()
                ->whereKey($member->id)
                ->lockForUpdate()
                ->firstOrFail();

            $share = $member->shares()
                ->where('status', 'pending')
                ->oldest('id')
                ->lockForUpdate()
                ->first();

            if (!$share) {
                return null;
            }

            $share->update([
                'status' => 'active',
                'acquired_date' => $share->acquired_date
                    ?? now()->toDateString(),
                'created_by' => $share->created_by
                    ?? $userId
                    ?? auth()->id(),
            ]);

            $this->forgetMemberCaches();

            return $share->fresh([
                'member.user',
                'creator',
            ]);
        });
    }

    public function approveMember(
        Member $member,
        ?int $approvedBy = null
    ): Member {
        return DB::transaction(function () use (
            $member,
            $approvedBy
        ) {
            $member = Member::query()
                ->with('user')
                ->whereKey($member->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($member->status === 'active') {
                return $member->load([
                    'user.roles',
                    'shares.creator',
                    'subscriptions.plan',
                ]);
            }

            if (in_array(
                $member->status,
                ['rejected'],
                true
            )) {
                throw ValidationException::withMessages([
                    'member' => [
                        'Rejected member cannot be approved directly.'
                    ],
                ]);
            }

            if (!$member->user) {
                throw ValidationException::withMessages([
                    'member' => [
                        'Member user account was not found.'
                    ],
                ]);
            }

            $member->update([
                'status' => 'active',
                'joining_date' => $member->joining_date
                    ?? now()->toDateString(),
            ]);

            $member->user->update([
                'is_active' => true,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Activate Initial Share
        |--------------------------------------------------------------------------
        |
        | Share must be activated BEFORE generating the current subscription due,
        | because monthly subscription amount depends on active share count.
        |
        */
            if ($this->shareEnabled()) {
                $pendingShare = $member->shares()
                    ->where('status', 'pending')
                    ->oldest('id')
                    ->lockForUpdate()
                    ->first();

                if (!$pendingShare) {
                    throw ValidationException::withMessages([
                        'share' => [
                            'Pending initial share was not found for this member.'
                        ],
                    ]);
                }

                $pendingShare->update([
                    'status' => 'active',
                    'acquired_date' => $pendingShare->acquired_date
                        ?? $member->joining_date
                        ?? now()->toDateString(),
                    'created_by' => $pendingShare->created_by
                        ?? $approvedBy
                        ?? auth()->id(),
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Assign Default Subscription + Generate Current Month Due
        |--------------------------------------------------------------------------
        */

            $this->subscriptionService
                ->assignDefaultSubscription(
                    $member,
                    $approvedBy,
                    true
                );

            /*
        |--------------------------------------------------------------------------
        | Clear Authorization & Dashboard Cache
        |--------------------------------------------------------------------------
        */

            $member->user->forgetAuthorizationCache();

            $this->forgetMemberCaches();

            return $member->fresh([
                'user.roles',
                'shares.creator',
                'subscriptions.plan',
                'subscriptions.dues',
            ]);
        });
    }

    protected function createInitialShare(
        Member $member,
        bool $autoActivate = false,
        ?int $createdBy = null
    ): MemberShare {
        $existingShare = MemberShare::query()
            ->where('member_id', $member->id)
            ->first();

        if ($existingShare) {
            return $existingShare;
        }

        $shareValue = $this->defaultShareValue();

        return MemberShare::create([
            'member_id' => $member->id,
            'share_no' => $this->generateShareNumber(),
            'purchase_amount' => $shareValue,
            'acquired_date' => $autoActivate
                ? now()->toDateString()
                : null,
            'status' => $autoActivate
                ? 'active'
                : 'pending',
            'created_by' => $createdBy,
            'notes' => 'Initial membership share.',
        ]);
    }

    protected function shareEnabled(): bool
    {
        return filter_var(
            setting(
                'share_enabled',
                false
            ),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    protected function autoActivateMember(): bool
    {
        return filter_var(
            setting(
                'auto_activate_member',
                false
            ),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    protected function defaultShareValue(): float
    {
        $value = round(
            (float)setting(
                'default_share_value',
                50000
            ),
            2
        );

        if ($value <= 0) {
            throw ValidationException::withMessages([
                'share' => [
                    'Default share value must be greater than zero.'
                ],
            ]);
        }

        return $value;
    }

    protected function generateMemberCode(): string
    {
        $lastMember = Member::query()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $nextNumber = $lastMember
            ? $lastMember->id + 1
            : 1;

        $prefix = trim(
            (string)setting(
                'member_code_prefix',
                'DA'
            )
        );

        $prefix = $prefix !== ''
            ? strtoupper($prefix)
            : 'DA';

        $memberCode = $prefix . '-' . str_pad(
            (string)$nextNumber,
            6,
            '0',
            STR_PAD_LEFT
        );

        while (
            Member::query()
            ->where(
                'member_code',
                $memberCode
            )
            ->exists()
        ) {
            $nextNumber++;

            $memberCode = $prefix . '-' . str_pad(
                (string)$nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );
        }

        return $memberCode;
    }

    protected function generateShareNumber(): string
    {
        // NOTE: this must use the same shared, row-locked counter as
        // MemberShareService::generateShareNumber() (sequence key
        // 'member-share'). Previously this method generated numbers
        // independently from MemberShare::id+1, while the purchase/approval
        // flow in MemberShareService drew from the `number_sequences`
        // table. The two counters could drift apart and issue the same
        // share_no twice (e.g. SH-000015), causing a duplicate-key error.
        // Routing both through NumberSequenceService::next() (which locks
        // the sequence row for update) guarantees uniqueness.
        return $this->numberSequenceService->next(
            key: 'member-share',
            prefix: 'SH-',
            digits: 6,
            initialValue: function () {
                $last = MemberShare::query()
                    ->where(
                        'share_no',
                        'like',
                        'SH-%'
                    )
                    ->orderByDesc('id')
                    ->value('share_no');

                return $last
                    ? (int)substr(
                        $last,
                        -6
                    )
                    : 0;
            }
        );
    }

    public function forgetMemberCaches(): void
    {
        Cache::forget(
            'members:summary'
        );

        Cache::forget(
            'dashboard.members.summary'
        );

        Cache::forget(
            'dashboard.members.active_count'
        );

        Cache::forget(
            'dashboard.members.pending_count'
        );

        Cache::forget(
            'finance:dashboard'
        );

        $this->dashboardService
            ->forgetDashboardCaches();
    }
}
