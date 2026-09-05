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
        protected NumberSequenceService $numberSequenceService,
        protected AccountingService $accounting
    ) {}

    public function createMember(array $data): Member
    {
        $storedProfilePhoto = null;
        $storedNidDocument = null;

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

            if (
                isset($data['nid_document']) &&
                $data['nid_document'] instanceof UploadedFile
            ) {
                $storedNidDocument = $data['nid_document']->store(
                    'members/nid-documents',
                    'public'
                );

                $data['nid_document'] = $storedNidDocument;
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
                    'must_change_password' => true,
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
                    'father_or_husband_name' => $data['father_or_husband_name'] ?? null,
                    'mother_name' => $data['mother_name'] ?? null,
                    'phone' => $data['phone']
                        ?? $data['mobile']
                        ?? null,
                    'alternate_phone' => $data['alternate_phone'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'nid_or_birth_reg_no' => $data['nid_or_birth_reg_no'] ?? null,
                    'nid_document' => $data['nid_document'] ?? null,
                    'address' => $data['address'] ?? null,
                    'permanent_address' => $data['permanent_address'] ?? null,
                    'profession' => $data['profession'] ?? null,
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
                        $user->id,
                        $data['initial_share_amount'] ?? null,
                        $data['initial_share_payment_method'] ?? null
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

            if (
                $storedNidDocument &&
                Storage::disk('public')->exists(
                    $storedNidDocument
                )
            ) {
                Storage::disk('public')->delete(
                    $storedNidDocument
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
        | Post Accounting For Pending Initial Share + Activate It
        |--------------------------------------------------------------------------
        |
        | The initial share is created (pending) at member-creation time with
        | its final amount already set; the physical money is collected before
        | approval. So on approval we post that existing amount to accounting
        | (same double-entry pattern as MemberShareService::finalizeApproval)
        | and only then activate the share. Share must be activated BEFORE
        | generating the current subscription due, because monthly subscription
        | amount depends on active share count.
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

                $financeTransactionId = $pendingShare->finance_transaction_id;

                // Duplicate prevention: only post accounting if this pending
                // share doesn't already carry a finance transaction reference
                // (defensive — AccountingService::post()'s idempotency_key
                // also guards against a duplicate posting on retry).
                if (!$financeTransactionId) {
                    $amount = round(
                        (float) $pendingShare->purchase_amount,
                        2
                    );

                    if ($amount <= 0) {
                        throw ValidationException::withMessages([
                            'share' => [
                                'Initial share amount is invalid.'
                            ],
                        ]);
                    }

                    $receiveAccount = $this->resolveInitialShareReceiveAccount(
                        $pendingShare->payment_method
                    );

                    $capitalAccount = $this->accounting
                        ->account('member_equity');

                    $memberName = $member->user?->name
                        ?? $member->member_code
                        ?? 'Member';

                    $transaction = $this->accounting->post([
                        'idempotency_key' =>
                            "member-share:initial:{$pendingShare->id}",

                        'transaction_date' => now()->toDateString(),

                        'type' => 'member_initial_share',

                        'source_module' => 'member_share',

                        'source_id' => $pendingShare->id,

                        'reference_type' => MemberShare::class,

                        'reference_id' => $pendingShare->id,

                        'description' =>
                            "Initial share {$pendingShare->share_no} - {$memberName}",

                        'user_id' => $approvedBy,

                        'entries' => [
                            [
                                'account_id' => $receiveAccount->id,
                                'debit' => $amount,
                                'credit' => 0,
                                'description' =>
                                    "Initial share received - {$pendingShare->share_no}",
                            ],
                            [
                                'account_id' => $capitalAccount->id,
                                'debit' => 0,
                                'credit' => $amount,
                                'description' =>
                                    "Association capital - {$pendingShare->share_no}",
                            ],
                        ],
                    ]);

                    $financeTransactionId = $transaction->id;
                }

                $pendingShare->update([
                    'status' => 'active',
                    'acquired_date' => $pendingShare->acquired_date
                        ?? $member->joining_date
                        ?? now()->toDateString(),
                    'created_by' => $pendingShare->created_by
                        ?? $approvedBy
                        ?? auth()->id(),
                    'finance_transaction_id' => $financeTransactionId,
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
        ?int $createdBy = null,
        ?float $amount = null,
        ?string $paymentMethod = null
    ): MemberShare {
        $existingShare = MemberShare::query()
            ->where('member_id', $member->id)
            ->first();

        if ($existingShare) {
            return $existingShare;
        }

        // Use the amount the admin actually entered on the Add Member form
        // (what this member is really paying now); only fall back to the
        // configured default share value when nothing was entered.
        $shareValue = $amount && $amount > 0
            ? round($amount, 2)
            : $this->defaultShareValue();

        $paymentMethod = in_array(
            $paymentMethod,
            ['cash', 'bank', 'mobile_banking', 'online'],
            true
        ) ? $paymentMethod : 'cash';

        return MemberShare::create([
            'member_id' => $member->id,
            'share_no' => $this->generateShareNumber(),
            'purchase_amount' => $shareValue,
            'payment_method' => $paymentMethod,
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

    /**
     * Resolve which posting account received the initial share's physical
     * payment. Mirrors MemberShareService::resolveReceiveAccount()'s
     * mapping. The initial share now records the payment method entered on
     * the Add Member form; this only falls back to the cash account for
     * legacy rows created before that field existed (null payment_method).
     */
    protected function resolveInitialShareReceiveAccount(
        ?string $paymentMethod
    ) {
        return match ($paymentMethod) {
            'bank',
            'mobile_banking',
            'online' => $this->accounting->account('bank'),

            default => $this->accounting->account('cash'),
        };
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