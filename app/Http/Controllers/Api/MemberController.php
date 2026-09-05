<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Role;
use App\Services\ApprovalService;
use App\Services\MemberService;
use App\Services\PasswordSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    public function __construct(
        protected MemberService $memberService,
        protected ApprovalService $approvalService,
        protected PasswordSetupService $passwordSetupService
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:150',
            'status' => 'nullable|in:active,pending,inactive,suspended,rejected',
            'per_page' => 'nullable|integer|min:5|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? null;
        $perPage = min((int)($validated['per_page'] ?? 15), 100);

        $query = Member::query()
            ->select([
                'id',
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
                'created_at',
                'updated_at'
            ])
            ->with([
                'user:id,name,email,mobile,language,is_active',
                'user.roles:id,name,display_name'
            ]);

        if ($search !== '') {
            $like = "%{$search}%";

            $query->where(function ($q) use ($like) {
                $q->where('member_code', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('alternate_phone', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('district', 'like', $like)
                    ->orWhereHas('user', function ($userQuery) use ($like) {
                        $userQuery->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('mobile', 'like', $like);
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $members = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

        $summary = Cache::remember('members:summary', now()->addMinutes(10), function () {
            $counts = Member::query()
                ->selectRaw("
                    COUNT(*) AS total,
                    SUM(status='active') AS active,
                    SUM(status='pending') AS pending,
                    SUM(status='suspended') AS suspended,
                    SUM(status IN ('inactive','rejected')) AS inactive
                ")
                ->first();

            return [
                'total' => (int)($counts->total ?? 0),
                'active' => (int)($counts->active ?? 0),
                'pending' => (int)($counts->pending ?? 0),
                'suspended' => (int)($counts->suspended ?? 0),
                'inactive' => (int)($counts->inactive ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $members,
            'summary' => $summary,
        ]);
    }

    public function show(Member $member)
    {
        return response()->json([
            'success' => true,
            'data' => $member->load([
                'user:id,name,email,mobile,language,is_active',
                'user.roles:id,name,display_name'
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email:rfc|max:255|unique:users,email',
            'mobile' => 'required|string|max:20|unique:users,mobile',
            'profile_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'language' => 'nullable|in:en,bn',
            'father_or_husband_name' => 'required|string|max:150',
            'mother_name' => 'required|string|max:150',
            'alternate_phone' => 'required|string|regex:/^01[3-9][0-9]{8}$/',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'nid_or_birth_reg_no' => 'required|string|max:50',
            'nid_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'address' => 'required|string|max:2000',
            'permanent_address' => 'required|string|max:2000',
            'profession' => 'required|string|max:150',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'notes' => 'nullable|string|max:5000',
            'initial_share_amount' => 'nullable|numeric|min:0.01',
            'initial_share_payment_method' => 'nullable|in:cash,bank,mobile_banking,online',
        ]);

        $autoActivate = filter_var(
            setting('auto_activate_member', false),
            FILTER_VALIDATE_BOOLEAN
        );

        $member = null;
        $approval = null;

        try {
            [$member, $approval] = DB::transaction(function () use ($validated, $autoActivate, &$member, &$approval) {
                $member = $this->memberService->createMember($validated);
                $approval = null;

                if (!$autoActivate) {
                    $approval = $this->approvalService->createRequest(
                        $member,
                        'Member',
                        'create',
                        auth()->id(),
                        'New member registration requires approval.'
                    );
                }

                return [$member, $approval];
            });
        } catch (\Throwable $e) {
            if (
                $member?->profile_photo &&
                Storage::disk('public')->exists($member->profile_photo)
            ) {
                Storage::disk('public')->delete($member->profile_photo);
            }

            throw $e;
        }

        if ($autoActivate) {
            try {
                $member->loadMissing('user');

                if ($member->user) {
                    $this->passwordSetupService->send($member->user);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $autoActivate
                ? 'Member created and activated successfully.'
                : 'Member created successfully and sent for approval.',
            'data' => [
                'member' => $member
                    ->loadMissing('user.roles'),
                'approval' => $approval,
            ],
        ], 201);
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'father_or_husband_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|max:30',
            'nid_or_birth_reg_no' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:2000',
            'permanent_address' => 'nullable|string|max:2000',
            'profession' => 'nullable|string|max:150',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:5000',
        ]);

        $member->update($validated);
        $this->memberService->forgetMemberCaches();

        return response()->json([
            'success' => true,
            'message' => 'Member updated successfully.',
            'data' => $member->fresh()->load([
                'user:id,name,email,mobile,language,is_active',
                'user.roles:id,name,display_name'
            ]),
        ]);
    }

    public function destroy(Member $member)
    {
        if ($member->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Active members cannot be deleted. Suspend the member first.',
            ], 422);
        }

        DB::transaction(function () use ($member) {
            $user = $member->user;
            $member->delete();

            if ($user) {
                $user->update(['is_active' => false]);
            }

            $this->memberService->forgetMemberCaches();
        });

        return response()->json([
            'success' => true,
            'message' => 'Member deleted successfully.',
        ]);
    }

    public function suspend(Member $member)
    {
        if ($member->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active members can be suspended.',
            ], 422);
        }

        DB::transaction(function () use ($member) {
            $member = Member::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($member->id);

            $member->update(['status' => 'suspended']);

            if ($member->user) {
                $member->user->update(['is_active' => false]);
            }

            $this->memberService->forgetMemberCaches();
        });

        return response()->json([
            'success' => true,
            'message' => 'Member suspended successfully.',
            'data' => $member->fresh()->load(['user.roles']),
        ]);
    }

    public function activate(Member $member)
    {
        if (!in_array($member->status, ['inactive', 'suspended'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only inactive or suspended members can be activated.',
            ], 422);
        }

        DB::transaction(function () use ($member) {
            $member = Member::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($member->id);

            if (!$member->user) {
                throw ValidationException::withMessages([
                    'member' => ['No linked user account found.'],
                ]);
            }

            $member->update([
                'status' => 'active',
                'joining_date' => $member->joining_date ?? now()->toDateString(),
            ]);

            $member->user->update(['is_active' => true]);

            $this->memberService->forgetMemberCaches();
        });

        return response()->json([
            'success' => true,
            'message' => 'Member activated successfully.',
            'data' => $member->fresh()->load(['user.roles']),
        ]);
    }

    public function roles(Member $member)
    {
        $member->load([
            'user.roles:id,name,display_name',
        ]);

        $availableRoles = Role::query()
            ->select('id', 'name', 'display_name')
            ->orderBy('display_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'assigned_roles' => $member->user?->roles ?? [],
                'available_roles' => $availableRoles,
            ],
        ]);
    }

    public function assignRole(Request $request, Member $member)
    {
        $validated = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        if (!$member->user) {
            return response()->json([
                'success' => false,
                'message' => 'No linked user account found.',
            ], 422);
        }

        $role = Role::findOrFail($validated['role_id']);

        DB::transaction(function () use ($member, $role) {
            $member->user->assignRole($role);

            /*
         * Keep legacy role_id synchronized temporarily.
         * This will be removed after old single-role code is fully migrated.
         */
            if ($role->name !== 'member') {
                $member->user->update([
                    'role_id' => $role->id,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully.',
            'data' => $member->fresh()->load([
                'user.roles:id,name,display_name',
            ]),
        ]);
    }

    public function removeRole(Request $request, Member $member)
    {
        $validated = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        if (!$member->user) {
            return response()->json([
                'success' => false,
                'message' => 'No linked user account found.',
            ], 422);
        }

        $role = Role::findOrFail($validated['role_id']);

        if ($role->name === 'member') {
            return response()->json([
                'success' => false,
                'message' => 'The default Member role cannot be removed.',
            ], 422);
        }

        if ($role->name === 'system_analyst' && $member->user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot remove your own System Analyst role.',
            ], 422);
        }

        DB::transaction(function () use ($member, $role) {
            $member->user->removeRole($role);

            $remainingRole = $member->user->roles()
                ->where('roles.name', '!=', 'member')
                ->first();

            $memberRole = $member->user->roles()
                ->where('roles.name', 'member')
                ->first();

            $member->user->update([
                'role_id' => $remainingRole?->id ?? $memberRole?->id,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Role removed successfully.',
            'data' => $member->fresh()->load([
                'user.roles:id,name,display_name',
            ]),
        ]);
    }

    public function sendPasswordSetup(Member $member)
    {
        $member->load('user');

        if (!$member->user) {
            return response()->json([
                'success' => false,
                'message' => 'No linked user account found.',
            ], 422);
        }

        if ($member->status !== 'active' || !$member->user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Password setup link can only be sent to active members.',
            ], 422);
        }

        if (!$member->user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Member does not have an email address.',
            ], 422);
        }

        try {
            $this->passwordSetupService->resend(
                $member->user
            );

            return response()->json([
                'success' => true,
                'message' => 'Password setup email sent successfully.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send password setup email.',
            ], 500);
        }
    }
}