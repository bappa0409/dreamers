<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\Member;
use App\Services\MeetingService;
use Illuminate\Http\Request;

class MeetingAttendeeController extends Controller
{
    public function __construct(
        protected MeetingService $meetingService
    ){}

    public function store(Request $request,Meeting $meeting)
    {
        $data=$request->validate([
            'member_id'=>'required|exists:members,id',
            'status'=>'nullable|in:invited,present,absent,excused',
            'notes'=>'nullable|string|max:3000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Attendee added.',
            'data'=>$this->meetingService->addAttendee(
                $meeting,
                Member::findOrFail($data['member_id']),
                $data
            )
        ],201);
    }

    public function update(Request $request,MeetingAttendee $attendee)
    {
        $data=$request->validate([
            'status'=>'required|in:invited,present,absent,excused',
            'notes'=>'nullable|string|max:3000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Attendance updated.',
            'data'=>$this->meetingService->updateAttendee($attendee,$data)
        ]);
    }

    public function destroy(MeetingAttendee $attendee)
    {
        $this->meetingService->removeAttendee($attendee);

        return response()->json([
            'success'=>true,
            'message'=>'Attendee removed.'
        ]);
    }
}