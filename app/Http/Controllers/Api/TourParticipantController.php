<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Tour;
use App\Models\TourParticipant;
use App\Services\TourService;
use Illuminate\Http\Request;

class TourParticipantController extends Controller
{
    public function __construct(protected TourService $tourService){}

    public function store(Request $request,Tour $tour)
    {
        $data=$request->validate([
            'member_id'=>'required|integer|exists:members,id',
            'status'=>'nullable|in:registered,confirmed,attended,absent,cancelled',
            'notes'=>'nullable|string|max:3000'
        ]);

        $member=Member::findOrFail($data['member_id']);

        return response()->json([
            'success'=>true,
            'message'=>'Participant added successfully.',
            'data'=>$this->tourService->addParticipant($tour,$member,$data)
        ],201);
    }

    public function update(Request $request,TourParticipant $participant)
    {
        $data=$request->validate([
            'status'=>'required|in:registered,confirmed,attended,absent,cancelled',
            'notes'=>'nullable|string|max:3000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Participant updated successfully.',
            'data'=>$this->tourService->updateParticipant($participant,$data)
        ]);
    }

    public function destroy(TourParticipant $participant)
    {
        $this->tourService->removeParticipant($participant);

        return response()->json([
            'success'=>true,
            'message'=>'Participant removed successfully.'
        ]);
    }
}