<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:pending,approved,rejected,cancelled',
            'module'=>'nullable|string|max:100',
            'per_page'=>'nullable|integer|min:5|max:100',
            'page'=>'nullable|integer|min:1',
        ]);

        $search=trim($validated['search']??'');
        $status=$validated['status']??null;
        $module=$validated['module']??null;
        $perPage=min((int)($validated['per_page']??20),100);

        $query=ApprovalRequest::query()
            ->with([
                'requester:id,name,email',
                'approver:id,name,email',
                'rejecter:id,name,email',
                'canceller:id,name,email',
                'approvable'
            ]);

        if($status){
            $query->where('status',$status);
        }

        if($module){
            $query->where('module',$module);
        }

        if($search!==''){
            $like="%{$search}%";

            $query->where(function($q)use($like){
                $q->where('module','like',$like)
                    ->orWhere('action','like',$like)
                    ->orWhere('request_note','like',$like)
                    ->orWhere('rejection_reason','like',$like)
                    ->orWhere('cancellation_reason','like',$like)
                    ->orWhereHas('requester',function($uq)use($like){
                        $uq->where('name','like',$like)
                            ->orWhere('email','like',$like);
                    });
            });
        }

        $approvals=$query
            ->orderByRaw("CASE WHEN status='pending' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success'=>true,
            'data'=>$approvals
        ]);
    }

    public function show(ApprovalRequest $approval)
    {
        return response()->json([
            'success'=>true,
            'data'=>$approval->load([
                'approvable',
                'requester:id,name,email',
                'approver:id,name,email',
                'rejecter:id,name,email',
                'canceller:id,name,email'
            ])
        ]);
    }

    public function statistics()
    {
        $statistics=Cache::remember(
            'approvals:statistics',
            now()->addMinutes(5),
            function(){
                $row=ApprovalRequest::query()
                    ->selectRaw("
                        COUNT(*) AS total,
                        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending,
                        SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved,
                        SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) AS rejected,
                        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled
                    ")
                    ->first();

                return [
                    'total'=>(int)($row->total??0),
                    'pending'=>(int)($row->pending??0),
                    'approved'=>(int)($row->approved??0),
                    'rejected'=>(int)($row->rejected??0),
                    'cancelled'=>(int)($row->cancelled??0),
                ];
            }
        );

        return response()->json([
            'success'=>true,
            'data'=>$statistics
        ]);
    }

    public function approve(ApprovalRequest $approval)
    {
        $approval=$this->approvalService->approve(
            $approval,
            auth()->id()
        );

        return response()->json([
            'success'=>true,
            'message'=>'Approval request approved successfully.',
            'data'=>$approval
        ]);
    }

    public function reject(Request $request,ApprovalRequest $approval)
    {
        $validated=$request->validate([
            'reason'=>'required|string|max:2000'
        ]);

        $approval=$this->approvalService->reject(
            $approval,
            auth()->id(),
            $validated['reason']
        );

        return response()->json([
            'success'=>true,
            'message'=>'Approval request rejected successfully.',
            'data'=>$approval
        ]);
    }

    public function cancel(Request $request,ApprovalRequest $approval)
    {
        $validated=$request->validate([
            'reason'=>'nullable|string|max:2000'
        ]);

        $approval=$this->approvalService->cancel(
            $approval,
            auth()->id(),
            $validated['reason']??null
        );

        return response()->json([
            'success'=>true,
            'message'=>'Approval request cancelled successfully.',
            'data'=>$approval
        ]);
    }
}