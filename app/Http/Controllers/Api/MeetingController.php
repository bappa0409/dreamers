<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\User;
use App\Services\MeetingService;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function __construct(
        protected MeetingService $meetingService
    ){}

    public function index(Request $request)
    {
        $query=Meeting::query()
            ->with([
                'creator:id,name',
                'completer:id,name'
            ])
            ->withCount([
                'attendees',
                'decisions'
            ])
            ->withSum([
                'expenses as actual_expense'=>fn($q)=>$q->where('status','posted')
            ],'amount');

        if($search=trim((string)$request->search)){
            $query->where(function($q)use($search){
                $q->where('meeting_no','like',"%{$search}%")
                    ->orWhere('title','like',"%{$search}%")
                    ->orWhere('venue','like',"%{$search}%");
            });
        }

        if($request->filled('status')){
            $query->where('status',$request->status);
        }

        if($request->filled('type')){
            $query->where('type',$request->type);
        }

        if($request->filled('year')){
            $query->whereYear('meeting_date',(int)$request->year);
        }

        return response()->json([
            'success'=>true,
            'data'=>$query
                ->latest('meeting_date')
                ->latest('id')
                ->paginate(min((int)$request->input('per_page',15),50))
        ]);
    }

    public function show(Meeting $meeting)
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->meetingService->updateMinutes(
                $meeting,
                $meeting->minutes
            )
        ]);
    }

    public function store(Request $request)
    {
        $data=$request->validate([
            'title'=>'required|string|max:255',
            'type'=>'required|in:general,annual,executive,emergency,special',
            'meeting_date'=>'required|date',
            'start_time'=>'nullable|date_format:H:i',
            'end_time'=>'nullable|date_format:H:i',
            'venue'=>'nullable|string|max:255',
            'description'=>'nullable|string|max:5000',
            'budget_amount'=>'nullable|numeric|min:0',
            'notes'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Meeting created successfully.',
            'data'=>$this->meetingService->create(
                $data,
                $request->user()->id
            )
        ],201);
    }

    public function update(Request $request,Meeting $meeting)
    {
        $data=$request->validate([
            'title'=>'required|string|max:255',
            'type'=>'required|in:general,annual,executive,emergency,special',
            'meeting_date'=>'required|date',
            'start_time'=>'nullable|date_format:H:i',
            'end_time'=>'nullable|date_format:H:i',
            'venue'=>'nullable|string|max:255',
            'description'=>'nullable|string|max:5000',
            'budget_amount'=>'nullable|numeric|min:0',
            'notes'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Meeting updated successfully.',
            'data'=>$this->meetingService->update($meeting,$data)
        ]);
    }

    public function schedule(Meeting $meeting)
    {
        return response()->json([
            'success'=>true,
            'message'=>'Meeting scheduled successfully.',
            'data'=>$this->meetingService->schedule($meeting)
        ]);
    }

    public function status(Request $request,Meeting $meeting)
    {
        $data=$request->validate([
            'status'=>'required|in:scheduled,ongoing,completed,cancelled'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Meeting status updated successfully.',
            'data'=>$this->meetingService->changeStatus(
                $meeting,
                $data['status'],
                $request->user()->id
            )
        ]);
    }

    public function minutes(Request $request,Meeting $meeting)
    {
        $data=$request->validate([
            'minutes'=>'nullable|string|max:20000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Meeting minutes updated.',
            'data'=>$this->meetingService->updateMinutes(
                $meeting,
                $data['minutes']??null
            )
        ]);
    }

    public function destroy(Meeting $meeting)
    {
        $this->meetingService->delete($meeting);

        return response()->json([
            'success'=>true,
            'message'=>'Meeting deleted successfully.'
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->meetingService->statistics()
        ]);
    }

    public function options()
    {
        return response()->json([
            'success'=>true,
            'data'=>[
                'members'=>Member::query()
                    ->where('status','active')
                    ->with('user:id,name')
                    ->select('id','user_id','member_code')
                    ->orderBy('member_code')
                    ->get(),
                'users'=>User::query()
                    ->where('is_active',true)
                    ->select('id','name','email')
                    ->orderBy('name')
                    ->get(),
                'expense_accounts'=>Account::query()
                    ->where('type','expense')
                    ->where('is_active',true)
                    ->select('id','code','name')
                    ->orderBy('code')
                    ->get(),
                'payment_accounts'=>Account::query()
                    ->whereIn('sub_type',['cash','bank'])
                    ->where('is_active',true)
                    ->select('id','code','name','sub_type')
                    ->orderBy('code')
                    ->get()
            ]
        ]);
    }
}