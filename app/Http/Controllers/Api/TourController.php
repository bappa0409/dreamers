<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Member;
use App\Models\Tour;
use App\Services\ApprovalService;
use App\Services\TourService;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function __construct(
        protected TourService $tourService,
        protected ApprovalService $approvalService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:draft,approved,upcoming,ongoing,completed,cancelled',
            'year'=>'nullable|integer|min:2000|max:2100',
            'per_page'=>'nullable|integer|min:5|max:50'
        ]);

        $query=Tour::query()
            ->with([
                'creator:id,name',
                'approver:id,name'
            ])
            ->withCount('participants')
            ->withSum([
                'expenses as actual_expense'=>fn($query)=>$query->where('status','posted')
            ],'amount');

        if($search=trim((string)($validated['search']??''))){
            $query->where(function($q)use($search){
                $q->where('tour_no','like',"%{$search}%")
                    ->orWhere('title','like',"%{$search}%")
                    ->orWhere('destination','like',"%{$search}%");
            });
        }

        if(!empty($validated['status'])){
            $query->where('status',$validated['status']);
        }

        if(!empty($validated['year'])){
            $query->whereYear('start_date',(int)$validated['year']);
        }

        return response()->json([
            'success'=>true,
            'data'=>$query
                ->latest('start_date')
                ->latest('id')
                ->paginate($validated['per_page']??15)
        ]);
    }

    public function show(Tour $tour)
    {
        $tour->load([
            'creator:id,name,email',
            'approver:id,name,email',
            'participants'=>fn($query)=>$query
                ->with('member.user:id,name,email')
                ->latest('id'),
            'expenses'=>fn($query)=>$query
                ->with([
                    'expenseAccount:id,code,name',
                    'paymentAccount:id,code,name',
                    'creator:id,name,email',
                    'financeTransaction.entries.account'
                ])
                ->latest('expense_date')
                ->latest('id')
        ]);

        $actual=round((float)$tour->expenses
            ->where('status','posted')
            ->sum('amount'),2);

        return response()->json([
            'success'=>true,
            'data'=>array_merge($tour->toArray(),[
                'actual_expense'=>$actual,
                'budget_variance'=>round((float)$tour->budget_amount-$actual,2)
            ])
        ]);
    }

    public function store(Request $request)
    {
        $data=$request->validate([
            'title'=>'required|string|max:255',
            'destination'=>'required|string|max:255',
            'description'=>'nullable|string|max:5000',
            'start_date'=>'required|date',
            'end_date'=>'required|date|after_or_equal:start_date',
            'budget_amount'=>'nullable|numeric|min:0|max:9999999999999.99',
            'notes'=>'nullable|string|max:5000'
        ]);

        $tour=$this->tourService->create($data,$request->user()->id);

        $approval=$this->approvalService->createRequest(
            $tour,
            'Tour',
            'approve',
            $request->user()->id,
            'New tour requires approval before it is announced.'
        );

        return response()->json([
            'success'=>true,
            'message'=>'Tour created successfully.',
            'data'=>$tour,
            'approval'=>$approval
        ],201);
    }

    public function update(Request $request,Tour $tour)
    {
        $data=$request->validate([
            'title'=>'required|string|max:255',
            'destination'=>'required|string|max:255',
            'description'=>'nullable|string|max:5000',
            'start_date'=>'required|date',
            'end_date'=>'required|date|after_or_equal:start_date',
            'budget_amount'=>'nullable|numeric|min:0|max:9999999999999.99',
            'notes'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Tour updated successfully.',
            'data'=>$this->tourService->update($tour,$data)
        ]);
    }

    public function approve(Request $request,Tour $tour)
    {
        $validated=$request->validate([
            'remarks'=>'nullable|string|max:2000'
        ]);

        $approvalRequest=$this->approvalService->findPendingRequestFor(
            $tour,
            'Tour',
            'approve'
        );

        $this->approvalService->approve(
            $approvalRequest,
            $request->user()->id,
            $validated['remarks']??null
        );

        return response()->json([
            'success'=>true,
            'message'=>'Tour approval step completed successfully.',
            'data'=>$this->tourService->fresh($tour)
        ]);
    }

    public function status(Request $request,Tour $tour)
    {
        $data=$request->validate([
            'status'=>'required|in:approved,upcoming,ongoing,completed,cancelled'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Tour status updated successfully.',
            'data'=>$this->tourService->changeStatus($tour,$data['status'])
        ]);
    }

    public function destroy(Tour $tour)
    {
        $this->tourService->delete($tour);

        return response()->json([
            'success'=>true,
            'message'=>'Tour deleted successfully.'
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->tourService->statistics()
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
                'expense_accounts'=>Account::query()
                    ->where('type','expense')
                    ->where('is_active',true)
                    ->whereDoesntHave('children')
                    ->select('id','code','name')
                    ->orderBy('code')
                    ->get(),
                'payment_accounts'=>Account::query()
                    ->where('type','asset')
                    ->whereIn('sub_type',['cash','bank'])
                    ->where('is_active',true)
                    ->whereDoesntHave('children')
                    ->select('id','code','name','sub_type')
                    ->orderBy('code')
                    ->get()
            ]
        ]);
    }
}