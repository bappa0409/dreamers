<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Services\DashboardService;

class MemberService
{

    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Create a new association member.
     *
     * Creates:
     * 1. User login account
     * 2. Member profile
     * 3. Default Member role
     *
     * The account remains inactive until
     * the membership is approved.
     */
    public function createMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $memberRole = Role::where('name', 'member')->first();

            if (!$memberRole) {
                throw ValidationException::withMessages([
                    'role' => ['Default Member role is not configured.'],
                ]);
            }

            $autoActivate = (bool)setting('auto_activate_member', false);
            $defaultLanguage = setting('default_language', 'en');

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'] ?? null,
                'password' => Hash::make(Str::random(64)),
                'password_setup_token' => null,
                'password_setup_expires_at' => null,
                'language' => $data['language'] ?? $defaultLanguage,
                'is_active' => $autoActivate,
                'role_id' => $memberRole->id,
            ]);

            $user->roles()->syncWithoutDetaching([$memberRole->id]);
            $user->forgetAuthorizationCache();

            $member = Member::create([
                'user_id' => $user->id,
                'member_code' => $this->generateMemberCode(),
                'phone' => $data['phone'] ?? $data['mobile'] ?? null,
                'alternate_phone' => $data['alternate_phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'district' => $data['district'] ?? null,
                'joining_date' => $autoActivate ? now()->toDateString() : null,
                'status' => $autoActivate ? 'active' : 'pending',
                'profile_photo' => $data['profile_photo'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->forgetMemberCaches();

            return $member->load('user.roles');
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Member Code Generator
    |--------------------------------------------------------------------------
    */

    protected function generateMemberCode(): string
    {
        $lastMember = Member::query()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $nextNumber = $lastMember ? $lastMember->id + 1 : 1;
        $prefix = trim((string)setting('member_code_prefix', 'DA'));
        $prefix = $prefix !== '' ? strtoupper($prefix) : 'DA';

        return $prefix . '-' . str_pad(
            (string)$nextNumber,
            6,
            '0',
            STR_PAD_LEFT
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Member Cache Invalidation
    |--------------------------------------------------------------------------
    |
    | Call this whenever member data changes:
    |
    | - create
    | - update
    | - approve
    | - reject
    | - activate
    | - suspend
    | - delete
    |
    */

    public function forgetMemberCaches(): void
    {
        Cache::forget('members:summary');
        Cache::forget('dashboard.members.summary');
        Cache::forget('dashboard.members.active_count');
        Cache::forget('dashboard.members.pending_count');

        $this->dashboardService->forgetDashboardCaches();
    }
}
