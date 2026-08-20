<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\Member;
use App\Models\Project;
use App\Models\Investment;
use App\Models\Land;
use App\Models\Notice;
use App\Services\ApprovalService;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'module'=>'nullable|string|max:100',
            'action'=>'nullable|string|max:50',
            'status'=>'nullable|in:pending,approved,rejected,cancelled',
            'my_pending'=>'nullable|boolean',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=ApprovalRequest::query()
            ->with([
                'requester:id,name,email',
                'approver:id,name,email',
                'rejecter:id,name,email',
                'steps.approver:id,name,email',
                'approvable'=>function(MorphTo $morphTo){
                    $morphTo->morphWith([
                        Member::class=>[
                            'user:id,name,email,mobile'
                        ],
                        Project::class=>[],
                        Investment::class=>[
                            'member.user:id,name,email'
                        ],
                        Land::class=>[],
                        Notice::class=>[],
                    ]);
                },
            ])
            ->latest('id');

        if(!empty($validated['module'])){
            $query->where(
                'module',
                $validated['module']
            );
        }

        if(!empty($validated['action'])){
            $query->where(
                'action',
                $validated['action']
            );
        }

        if(!empty($validated['status'])){
            $query->where(
                'status',
                $validated['status']
            );
        }

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where(
                    'module',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'action',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'request_note',
                    'like',
                    "%{$search}%"
                )
                ->orWhereHas(
                    'requester',
                    fn($user)=>
                        $user->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                );
            });
        }

        if($request->boolean('my_pending')){
            $userId=$request->user()->id;

            $query->where(
                'status',
                'pending'
            )
            ->whereHas(
                'steps',
                function($q)use($userId){
                    $q->where(
                        'approver_user_id',
                        $userId
                    )
                    ->where(
                        'status',
                        'pending'
                    )
                    ->whereColumn(
                        'approval_steps.step_no',
                        'approval_requests.current_step'
                    );
                }
            );
        }

        $perPage=min(
            (int)($validated['per_page']??15),
            100
        );

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate($perPage),
        ]);
    }

    public function show(
        ApprovalRequest $approvalRequest
    ){
        $approvalRequest->load([
            'requester:id,name,email',
            'approver:id,name,email',
            'rejecter:id,name,email',
            'canceller:id,name,email',
            'steps.approver:id,name,email',
            'approvable',
        ]);

        if(
            $approvalRequest->approvable instanceof Member
        ){
            $approvalRequest
                ->approvable
                ->loadMissing(
                    'user:id,name,email,mobile'
                );
        }

        return response()->json([
            'success'=>true,
            'data'=>$approvalRequest,
        ]);
    }

    public function approve(
        Request $request,
        ApprovalRequest $approvalRequest
    ){
        $validated=$request->validate([
            'remarks'=>'nullable|string|max:2000',
        ]);

        $approvalRequest=
            $this->approvalService
                ->approve(
                    $approvalRequest,
                    $request->user()->id,
                    $validated['remarks']??null
                );

        return response()->json([
            'success'=>true,

            'message'=>
                $approvalRequest->status==='approved'
                    ?'Request fully approved.'
                    :'Approval completed and forwarded to the next approver.',

            'data'=>$approvalRequest,
        ]);
    }

    public function reject(
        Request $request,
        ApprovalRequest $approvalRequest
    ){
        $validated=$request->validate([
            'reason'=>'required|string|max:2000',
            'remarks'=>'nullable|string|max:2000',
        ]);

        $approvalRequest=
            $this->approvalService
                ->reject(
                    $approvalRequest,
                    $request->user()->id,
                    $validated['reason'],
                    $validated['remarks']??null
                );

        return response()->json([
            'success'=>true,
            'message'=>'Request rejected successfully.',
            'data'=>$approvalRequest,
        ]);
    }

    public function cancel(
        Request $request,
        ApprovalRequest $approvalRequest
    ){
        $validated=$request->validate([
            'reason'=>'nullable|string|max:2000',
        ]);

        $approvalRequest=
            $this->approvalService
                ->cancel(
                    $approvalRequest,
                    $request->user()->id,
                    $validated['reason']??null
                );

        return response()->json([
            'success'=>true,
            'message'=>'Approval request cancelled successfully.',
            'data'=>$approvalRequest,
        ]);
    }
}