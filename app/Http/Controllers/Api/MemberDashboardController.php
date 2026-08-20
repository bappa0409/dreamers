<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Poll;
use App\Services\MemberDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberDashboardController extends Controller
{
    public function __construct(
        protected MemberDashboardService $memberDashboardService
    ) {}

    public function index(Request $request)
    {
        $member = $this->activeMember($request);

        return response()->json([
            'success' => true,
            'data' => [
                'member' => $member->load([
                    'user:id,name,email,mobile,language,is_active',
                    'subscriptions.plan',
                    'shares',
                ]),
                'summary' => $this->memberDashboardService->summary($member),
                'active_polls' => Poll::query()
                    ->where('is_active', true)
                    ->where('start_at', '<=', now())
                    ->where('end_at', '>=', now())
                    ->count(),
                'visible_notices' => Notice::query()
                    ->visible()
                    ->count(),
            ],
        ]);
    }

    public function profile(Request $request)
    {
        $member = $this->activeMember($request);

        return response()->json([
            'success' => true,
            'data' => [
                'member' => $member->load([
                    'user:id,name,email,mobile,language,is_active',
                ]),
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $member = $this->activeMember($request);

        $validated = $request->validate([
            'phone' => 'nullable|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_profile_photo' => 'nullable|boolean',
        ]);

        $oldPhoto = $member->profile_photo;
        $newPhoto = null;

        try {
            if ($request->boolean('remove_profile_photo')) {
                $validated['profile_photo'] = null;
            } elseif ($request->hasFile('profile_photo')) {
                $newPhoto = $request->file('profile_photo')->store(
                    'members/profile-photos',
                    'public'
                );

                $validated['profile_photo'] = $newPhoto;
            } else {
                unset($validated['profile_photo']);
            }

            unset($validated['remove_profile_photo']);

            $member->update($validated);

            if (
                ($newPhoto || $request->boolean('remove_profile_photo')) &&
                $oldPhoto &&
                Storage::disk('public')->exists($oldPhoto)
            ) {
                Storage::disk('public')->delete($oldPhoto);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => [
                    'member' => $member->fresh([
                        'user:id,name,email,mobile,language,is_active',
                    ]),
                ],
            ]);
        } catch (\Throwable $e) {
            if (
                $newPhoto &&
                Storage::disk('public')->exists($newPhoto)
            ) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $e;
        }
    }

    public function polls(Request $request)
    {
        $member = $this->activeMember($request);

        $polls = Poll::query()
            ->with([
                'options',
                'votes' => fn($q) => $q->where('member_id', $member->id),
            ])
            ->where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $polls,
        ]);
    }

    public function notices(Request $request)
    {
        $this->activeMember($request);

        $notices = Notice::query()
            ->visible()
            ->with('creator:id,name,email')
            ->latest('id')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notices,
        ]);
    }

    protected function activeMember(Request $request)
    {
        $member = $request->user()->member;

        abort_unless(
            $member && $member->status === 'active',
            403,
            'Active membership is required.'
        );

        return $member;
    }
}
