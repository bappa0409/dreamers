<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Poll;
use App\Services\PollService;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function __construct(
        protected PollService $pollService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'state'=>'nullable|in:active,upcoming,ended,inactive',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=Poll::query()
            ->with([
                'creator:id,name,email',
                'options'
            ])
            ->withCount('votes')
            ->latest('id');

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('title','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%");
            });
        }

        if(!empty($validated['state'])){
            $state=$validated['state'];

            if($state==='inactive'){
                $query->where('is_active',false);
            }

            if($state==='upcoming'){
                $query->where('is_active',true)
                    ->where('start_at','>',now());
            }

            if($state==='active'){
                $query->where('is_active',true)
                    ->where('start_at','<=',now())
                    ->where('end_at','>=',now());
            }

            if($state==='ended'){
                $query->where('is_active',true)
                    ->where('end_at','<',now());
            }
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                min((int)($validated['per_page']??10),100)
            )
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->pollService->statistics()
        ]);
    }

    public function store(Request $request)
    {
        $validated=$this->validatePoll($request);

        $poll=$this->pollService->create(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Poll created successfully.',
            'data'=>$poll->load('options')
        ],201);
    }

    public function show(Poll $poll)
    {
        return response()->json([
            'success'=>true,
            'data'=>$poll->load([
                'options',
                'creator:id,name,email'
            ])->loadCount('votes')
        ]);
    }

    public function update(Request $request,Poll $poll)
    {
        $validated=$this->validatePoll(
            $request,
            true
        );

        $poll=$this->pollService->update(
            $poll,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Poll updated successfully.',
            'data'=>$poll
        ]);
    }

    public function toggle(Poll $poll)
    {
        $poll=$this->pollService->update(
            $poll,
            [
                'is_active'=>!$poll->is_active
            ]
        );

        return response()->json([
            'success'=>true,
            'message'=>$poll->is_active
                ?'Poll activated successfully.'
                :'Poll deactivated successfully.',
            'data'=>$poll
        ]);
    }

    public function vote(Request $request,Poll $poll)
    {
        $validated=$request->validate([
            'poll_option_id'=>'required|integer|exists:poll_options,id'
        ]);

        $member=Member::query()
            ->where('user_id',$request->user()->id)
            ->first();

        if(!$member){
            return response()->json([
                'message'=>'Member profile not found.'
            ],404);
        }

        $vote=$this->pollService->vote(
            $poll,
            (int)$validated['poll_option_id'],
            $member
        );

        return response()->json([
            'success'=>true,
            'message'=>'Vote submitted successfully.',
            'data'=>$vote
        ],201);
    }

    public function results(Poll $poll)
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->pollService->results($poll)
        ]);
    }

    public function destroy(Poll $poll)
    {
        $this->pollService->delete($poll);

        return response()->json([
            'success'=>true,
            'message'=>'Poll deleted successfully.'
        ]);
    }

    protected function validatePoll(
        Request $request,
        bool $update=false
    ): array{
        $required=$update
            ?'sometimes|required'
            :'required';

        return $request->validate([
            'title'=>"{$required}|string|max:255",
            'description'=>'nullable|string|max:5000',
            'start_at'=>"{$required}|date",
            'end_at'=>"{$required}|date|after:start_at",
            'is_active'=>'nullable|boolean',
            'options'=>$update
                ?'sometimes|required|array|min:2'
                :'required|array|min:2',
            'options.*'=>'required|string|max:255'
        ]);
    }
}