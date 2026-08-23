<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingDecision;
use App\Services\MeetingService;
use Illuminate\Http\Request;

class MeetingDecisionController extends Controller
{
    public function __construct(
        protected MeetingService $meetingService
    ){}

    public function store(Request $request,Meeting $meeting)
    {
        $data=$request->validate([
            'meeting_agenda_id'=>'nullable|exists:meeting_agendas,id',
            'title'=>'required|string|max:255',
            'decision'=>'required|string|max:10000',
            'result'=>'required|in:approved,rejected,deferred,noted',
            'votes_for'=>'nullable|integer|min:0',
            'votes_against'=>'nullable|integer|min:0',
            'votes_abstain'=>'nullable|integer|min:0',
            'responsible_user_id'=>'nullable|exists:users,id',
            'due_date'=>'nullable|date',
            'status'=>'nullable|in:pending,in_progress,completed,cancelled',
            'completion_notes'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Decision recorded.',
            'data'=>$this->meetingService->addDecision($meeting,$data)
        ],201);
    }

    public function update(Request $request,MeetingDecision $decision)
    {
        $data=$request->validate([
            'meeting_agenda_id'=>'nullable|exists:meeting_agendas,id',
            'title'=>'sometimes|required|string|max:255',
            'decision'=>'sometimes|required|string|max:10000',
            'result'=>'nullable|in:approved,rejected,deferred,noted',
            'votes_for'=>'nullable|integer|min:0',
            'votes_against'=>'nullable|integer|min:0',
            'votes_abstain'=>'nullable|integer|min:0',
            'responsible_user_id'=>'nullable|exists:users,id',
            'due_date'=>'nullable|date',
            'status'=>'nullable|in:pending,in_progress,completed,cancelled',
            'completion_notes'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Decision updated.',
            'data'=>$this->meetingService->updateDecision($decision,$data)
        ]);
    }

    public function destroy(MeetingDecision $decision)
    {
        $this->meetingService->deleteDecision($decision);

        return response()->json([
            'success'=>true,
            'message'=>'Decision deleted.'
        ]);
    }
}