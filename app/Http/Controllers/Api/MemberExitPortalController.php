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

        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:submitted,under_review,liabilities_pending,ready_for_approval,approved,settled,closed,rejected,cancelled',
            'per_page'=>'nullable|integer|min:5|max:50',
        ]);

        $base=$member->exits();

        $summary=(clone $base)
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('submitted','under_review','liabilities_pending','ready_for_approval','approved') THEN 1 ELSE 0 END) AS pending,
                COALESCE(SUM(total_liabilities),0) AS total_liabilities,
                COALESCE(SUM(share_refund),0) AS share_refund
            ")
            ->first();

        $exits=$base
            ->with([
                'items',
                'nomineeAllocations.nominee',
            ])
            ->when(
                $validated['status']??null,
                fn($q,$status)=>$q->where('status',$status)
            )
            ->when(
                $validated['search']??null,
                function($q,$search){
                    $search=trim($search);

                    $q->where(function($q)use($search){
                        $q->where('exit_no','like',"%{$search}%")
                            ->orWhere('reason','like',"%{$search}%");
                    });
                }
            )
            ->latest('id')
            ->paginate($validated['per_page']??10)
            ->withQueryString();

        return response()->json([
            'success'=>true,
            'data'=>$exits,
            'summary'=>[
                'total'=>(int)($summary->total??0),
                'pending'=>(int)($summary->pending??0),
                'total_liabilities'=>round((float)($summary->total_liabilities??0),2),
                'share_refund'=>round((float)($summary->share_refund??0),2),
            ],
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