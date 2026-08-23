<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingAgenda;
use App\Services\MeetingService;
use Illuminate\Http\Request;

class MeetingAgendaController extends Controller
{
    public function __construct(
        protected MeetingService $meetingService
    ){}

    public function store(Request $request,Meeting $meeting)
    {
        $data=$request->validate([
            'sort_order'=>'nullable|integer|min:1|max:999',
            'title'=>'required|string|max:255',
            'description'=>'nullable|string|max:5000',
            'status'=>'nullable|in:pending,discussed,deferred,cancelled'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Agenda added successfully.',
            'data'=>$this->meetingService->addAgenda($meeting,$data)
        ],201);
    }

    public function update(Request $request,MeetingAgenda $agenda)
    {
        $data=$request->validate([
            'sort_order'=>'nullable|integer|min:1|max:999',
            'title'=>'sometimes|required|string|max:255',
            'description'=>'nullable|string|max:5000',
            'status'=>'nullable|in:pending,discussed,deferred,cancelled'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Agenda updated.',
            'data'=>$this->meetingService->updateAgenda($agenda,$data)
        ]);
    }

    public function destroy(MeetingAgenda $agenda)
    {
        $this->meetingService->deleteAgenda($agenda);

        return response()->json([
            'success'=>true,
            'message'=>'Agenda deleted.'
        ]);
    }
}