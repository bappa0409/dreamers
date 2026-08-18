<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\LandInvestment;
use App\Models\ProjectMember;
use App\Models\PollVote;
use App\Models\Member;

class MemberDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $member = \App\Models\Member::where('user_id', $user->id)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found.'
            ], 404);
        }

        /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

        $profile = [
            'id' => $member->id,
            'member_code' => $member->member_code,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $member->phone,
            'alternate_phone' => $member->alternate_phone,
            'date_of_birth' => $member->date_of_birth,
            'gender' => $member->gender,
            'address' => $member->address,
            'city' => $member->city,
            'district' => $member->district,
            'joining_date' => $member->joining_date,
            'status' => $member->status,
        ];

        /*
    |--------------------------------------------------------------------------
    | Investments
    |--------------------------------------------------------------------------
    */

        $investments = \App\Models\Investment::where(
            'member_id',
            $member->id
        )->with('returns')->latest()->get();

        $investmentSummary = [
            'total_investments' => $investments->count(),
            'total_amount' => $investments->sum('amount'),
            'total_expected_return' => $investments->sum('expected_return'),
        ];

        /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */

        $projectMembers = \App\Models\ProjectMember::where(
            'member_id',
            $member->id
        )->with('project')->latest()->get();

        $projectSummary = [
            'total_projects' => $projectMembers->count(),
        ];

        /*
    |--------------------------------------------------------------------------
    | Land Investments
    |--------------------------------------------------------------------------
    */

        $landInvestments = \App\Models\LandInvestment::where(
            'member_id',
            $member->id
        )->with('land')->latest()->get();

        $landSummary = [
            'total_land_investments' => $landInvestments->count(),
            'total_amount' => $landInvestments->sum('amount'),
            'total_ownership_percentage' =>
            $landInvestments->sum('ownership_percentage'),
        ];

        /*
    |--------------------------------------------------------------------------
    | Polls
    |--------------------------------------------------------------------------
    */

        $activePolls = \App\Models\Poll::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with('options')
            ->latest()
            ->get();

        $myVotes = \App\Models\PollVote::where(
            'member_id',
            $member->id
        )->with('pollOption')->get();

        /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

        $unreadNotifications = $user->unreadNotifications()->count();

        $notifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            });

        /*
    |--------------------------------------------------------------------------
    | Notices
    |--------------------------------------------------------------------------
    */

        $notices = \App\Models\Notice::where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->latest('publish_at')
            ->limit(5)
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Final Dashboard Response
    |--------------------------------------------------------------------------
    */

        return response()->json([
            'message' => 'Member dashboard retrieved successfully.',

            'data' => [

                'profile' => $profile,

                'investment_summary' => $investmentSummary,

                'project_summary' => $projectSummary,

                'land_summary' => $landSummary,

                'poll_summary' => [
                    'active_polls' => $activePolls->count(),
                    'total_votes' => $myVotes->count(),
                ],

                'notification_summary' => [
                    'unread_notifications' => $unreadNotifications,
                ],

                'investments' => $investments,

                'projects' => $projectMembers,

                'land_investments' => $landInvestments,

                'active_polls' => $activePolls,

                'voting_history' => $myVotes,

                'notifications' => $notifications,

                'notices' => $notices,
            ]
        ]);
    }

    public function profile()
    {
        $user = auth()->user();

        $member = Member::where('user_id', $user->id)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found.'
            ], 404);
        }

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'language' => $user->language,
                ],

                'member' => [
                    'id' => $member->id,
                    'member_code' => $member->member_code,
                    'phone' => $member->phone,
                    'alternate_phone' => $member->alternate_phone,
                    'date_of_birth' => $member->date_of_birth,
                    'gender' => $member->gender,
                    'address' => $member->address,
                    'city' => $member->city,
                    'district' => $member->district,
                    'joining_date' => $member->joining_date,
                    'status' => $member->status,
                    'profile_photo' => $member->profile_photo,
                    'notes' => $member->notes,
                ],
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $member = Member::where('user_id', $user->id)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found.'
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'alternate_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'nullable', 'string'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'district' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        if (isset($validated['name'])) {
            $user->update([
                'name' => $validated['name'],
            ]);
        }

        unset($validated['name']);

        $member->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'language' => $user->language,
                ],
                'member' => $member->fresh(),
            ],
        ]);
    }

    public function investments()
    {
        $user = auth()->user();

        $member = Member::where('user_id', $user->id)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found.'
            ], 404);
        }

        $investments = Investment::where('member_id', $member->id)
            ->with('returns')
            ->latest('investment_date')
            ->get();

        $totalAmount = (float) $investments->sum('amount');

        $totalExpectedReturn = (float) $investments->sum('expected_return');

        $totalPaidReturn = (float) $investments
            ->flatMap(fn($investment) => $investment->returns)
            ->where('status', 'paid')
            ->sum('amount');

        return response()->json([
            'data' => [
                'summary' => [
                    'total_investments' => $investments->count(),
                    'total_amount' => $totalAmount,
                    'total_expected_return' => $totalExpectedReturn,
                    'total_paid_return' => $totalPaidReturn,
                ],

                'investments' => $investments->map(function ($investment) {
                    return [
                        'id' => $investment->id,
                        'investment_no' => $investment->investment_no,
                        'title' => $investment->title,
                        'description' => $investment->description,
                        'amount' => (float) $investment->amount,
                        'expected_return' => (float) $investment->expected_return,
                        'investment_date' => $investment->investment_date,
                        'maturity_date' => $investment->maturity_date,
                        'status' => $investment->status,

                        'returns' => $investment->returns
                            ->map(function ($return) {
                                return [
                                    'id' => $return->id,
                                    'amount' => (float) $return->amount,
                                    'return_date' => $return->return_date,
                                    'description' => $return->description,
                                    'status' => $return->status,
                                ];
                            })
                            ->values()
                            ->toArray(),
                    ];
                })->values()->toArray(),
            ],
        ]);
    }

    public function projects()
    {
        $user = auth()->user();

        $member = Member::where('user_id', $user->id)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found.'
            ], 404);
        }

        $projects = ProjectMember::where('member_id', $member->id)
            ->with('project')
            ->latest('joined_date')
            ->get();

        $totalContribution = (float) $projects->sum('contribution');

        return response()->json([
            'data' => [
                'summary' => [
                    'total_projects' => $projects->count(),
                    'total_contribution' => $totalContribution,
                    'active_projects' => $projects
                        ->where('status', 'active')
                        ->count(),
                ],

                'projects' => $projects->map(function ($projectMember) {
                    $project = $projectMember->project;

                    return [
                        'id' => $projectMember->id,

                        'project' => $project ? [
                            'id' => $project->id,
                            'project_code' => $project->project_code,
                            'name' => $project->name,
                            'description' => $project->description,
                            'location' => $project->location,
                            'budget' => (float) $project->budget,
                            'actual_cost' => (float) $project->actual_cost,
                            'start_date' => $project->start_date,
                            'expected_end_date' => $project->expected_end_date,
                            'actual_end_date' => $project->actual_end_date,
                            'progress' => (float) $project->progress,
                            'status' => $project->status,
                        ] : null,

                        'contribution' => (float) $projectMember->contribution,
                        'role' => $projectMember->role,
                        'joined_date' => $projectMember->joined_date,
                        'status' => $projectMember->status,
                    ];
                })->values()->toArray(),
            ],
        ]);
    }

    public function landInvestments()
    {
        $user = auth()->user();

        $member = Member::where('user_id', $user->id)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found.'
            ], 404);
        }

        $investments = LandInvestment::where('member_id', $member->id)
            ->with('land')
            ->latest('investment_date')
            ->get();

        $totalAmount = (float) $investments->sum('amount');

        $totalOwnership = (float) $investments->sum('ownership_percentage');

        return response()->json([
            'data' => [
                'summary' => [
                    'total_land_investments' => $investments->count(),
                    'total_amount' => $totalAmount,
                    'total_ownership_percentage' => $totalOwnership,
                ],

                'investments' => $investments->map(function ($investment) {
                    $land = $investment->land;

                    return [
                        'id' => $investment->id,

                        'land' => $land ? [
                            'id' => $land->id,
                            'land_code' => $land->land_code,
                            'title' => $land->title,
                            'description' => $land->description,
                            'district' => $land->district,
                            'upazila' => $land->upazila,
                            'mouza' => $land->mouza,
                            'khatian_no' => $land->khatian_no,
                            'dag_no' => $land->dag_no,
                            'land_area' => (float) $land->land_area,
                            'area_unit' => $land->area_unit,
                            'purchase_price' => (float) $land->purchase_price,
                            'purchase_date' => $land->purchase_date,
                            'status' => $land->status,
                        ] : null,

                        'amount' => (float) $investment->amount,
                        'ownership_percentage' =>
                        (float) $investment->ownership_percentage,
                        'investment_date' => $investment->investment_date,
                        'status' => $investment->status,
                        'notes' => $investment->notes,
                    ];
                })->values()->toArray(),
            ],
        ]);
    }

    public function polls()
    {
        $user = auth()->user();

        $member = Member::where('user_id', $user->id)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found.'
            ], 404);
        }

        $activePolls = Poll::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with('options')
            ->latest()
            ->get();

        $votes = PollVote::where('member_id', $member->id)
            ->with(['poll', 'pollOption'])
            ->latest()
            ->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'active_polls' => $activePolls->count(),
                    'total_votes' => $votes->count(),
                ],

                'active_polls' => $activePolls->map(function ($poll) use ($member) {

                    $myVote = PollVote::where('poll_id', $poll->id)
                        ->where('member_id', $member->id)
                        ->first();

                    return [
                        'id' => $poll->id,
                        'title' => $poll->title,
                        'description' => $poll->description,
                        'start_at' => $poll->start_at,
                        'end_at' => $poll->end_at,
                        'is_active' => (bool) $poll->is_active,

                        'has_voted' => (bool) $myVote,

                        'my_vote_option_id' => $myVote?->poll_option_id,

                        'options' => $poll->options
                            ->map(function ($option) {
                                return [
                                    'id' => $option->id,
                                    'option_text' => $option->option_text,
                                    'sort_order' => $option->sort_order,
                                ];
                            })
                            ->values()
                            ->toArray(),
                    ];
                })->values()->toArray(),

                'voting_history' => $votes->map(function ($vote) {
                    return [
                        'id' => $vote->id,
                        'poll_id' => $vote->poll_id,
                        'poll_title' => $vote->poll?->title,
                        'option_id' => $vote->poll_option_id,
                        'option_text' => $vote->pollOption?->option_text,
                        'created_at' => $vote->created_at,
                    ];
                })->values()->toArray(),
            ],
        ]);
    }
}
