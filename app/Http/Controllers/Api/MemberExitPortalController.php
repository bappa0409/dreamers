<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberExit;
use App\Services\MemberExitService;
use Illuminate\Http\Request;

class MemberExitPortalController extends Controller
{
    public function __construct(
        protected MemberExitService $service
    ){}

    public function index(Request $request)
    {
        $member=$request->user()->member;

        abort_unless($member,403);

        return response()->json([
            'success'=>true,
            'data'=>$member->exits()
                ->with([
                    'items',
                    'nomineeAllocations.nominee',
                ])
                ->latest('id')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $member=$request->user()->member;

        abort_unless(
            $member&&
            in_array(
                $member->status,
                ['active','suspended','inactive'],
                true
            ),
            403
        );

        $validated=$request->validate([
            'reason'=>'required|string|max:5000',
            'proposed_exit_date'=>
                'nullable|date_format:Y-m-d|after_or_equal:today',
        ]);

        $validated['exit_type']='resignation';
        $validated['request_date']=now()
            ->toDateString();

        return response()->json([
            'success'=>true,
            'message'=>'Resignation request submitted.',
            'data'=>$this->service->initiate(
                $member,
                $validated,
                $request->user()->id,
                true
            ),
        ],201);
    }

    public function cancel(
        Request $request,
        MemberExit $memberExit
    ){
        abort_unless(
            $memberExit->member_id===
            $request->user()->member?->id,
            403
        );

        return response()->json([
            'success'=>true,
            'message'=>'Resignation request cancelled.',
            'data'=>$this->service->cancel(
                $memberExit,
                $request->user()->id,
                true
            ),
        ]);
    }
}