<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function index()
    {
        $polls = Poll::with('options')
            ->latest()
            ->paginate(10);

        return response()->json($polls);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
        ]);

        $poll = DB::transaction(function () use ($validated, $request) {

            $poll = Poll::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_at' => $validated['start_at'],
                'end_at' => $validated['end_at'],
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);

            foreach ($validated['options'] as $index => $option) {
                $poll->options()->create([
                    'option_text' => $option,
                    'sort_order' => $index,
                ]);
            }

            return $poll;
        });

        return response()->json([
            'message' => 'Poll created successfully.',
            'poll' => $poll->load('options'),
        ], 201);
    }

    public function show(Poll $poll)
    {
        return response()->json(
            $poll->load('options')
        );
    }

    public function update(Request $request, Poll $poll)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'sometimes|required|date',
            'end_at' => 'sometimes|required|date',
            'is_active' => 'sometimes|boolean',
        ]);

        $poll->update($validated);

        return response()->json([
            'message' => 'Poll updated successfully.',
            'poll' => $poll->load('options'),
        ]);
    }

    public function destroy(Poll $poll)
    {
        $poll->delete();

        return response()->json([
            'message' => 'Poll deleted successfully.',
        ]);
    }

    public function vote(Request $request, Poll $poll)
    {
        $validated = $request->validate([
            'poll_option_id' => 'required|exists:poll_options,id',
            'member_id' => 'required|exists:members,id',
        ]);

        if (!$poll->is_active) {
            return response()->json([
                'message' => 'This poll is inactive.'
            ], 422);
        }

        if (now()->lt($poll->start_at) || now()->gt($poll->end_at)) {
            return response()->json([
                'message' => 'Voting is not available at this time.'
            ], 422);
        }

        $optionExists = $poll->options()
            ->where('id', $validated['poll_option_id'])
            ->exists();

        if (!$optionExists) {
            return response()->json([
                'message' => 'Invalid poll option.'
            ], 422);
        }

        $alreadyVoted = PollVote::where('poll_id', $poll->id)
            ->where('member_id', $validated['member_id'])
            ->exists();

        if ($alreadyVoted) {
            return response()->json([
                'message' => 'You have already voted in this poll.'
            ], 422);
        }

        $vote = PollVote::create([
            'poll_id' => $poll->id,
            'poll_option_id' => $validated['poll_option_id'],
            'member_id' => $validated['member_id'],
        ]);

        return response()->json([
            'message' => 'Vote submitted successfully.',
            'vote' => $vote,
        ], 201);
    }
    public function results(Poll $poll)
{
    $results = $poll->options()
        ->withCount('votes')
        ->orderBy('sort_order')
        ->get();

    return response()->json([
        'poll' => $poll->only([
            'id',
            'title',
            'start_at',
            'end_at',
        ]),
        'total_votes' => $poll->votes()->count(),
        'results' => $results,
    ]);
}
}