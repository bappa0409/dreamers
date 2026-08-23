<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;

class MemberTourController extends Controller
{
    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:approved,upcoming,ongoing,completed',
            'year'=>'nullable|integer|min:2000|max:2100',
            'per_page'=>'nullable|integer|min:5|max:50',
        ]);

        $query=Tour::query()
            ->whereIn('status',['approved','upcoming','ongoing','completed'])
            ->withCount([
                'participants as participant_count'=>fn($q)=>$q->whereIn('status',['confirmed','attended']),
            ])
            ->withSum([
                'expenses as actual_expense'=>fn($q)=>$q->where('status','posted'),
            ],'amount')
            ->latest('start_date')
            ->latest('id');

        if(!empty($validated['search'])){
            $search=trim($validated['search']);

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
            $query->whereYear('start_date',$validated['year']);
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate($validated['per_page']??12),
        ]);
    }

    public function show(Tour $tour)
    {
        abort_unless(
            in_array($tour->status,['approved','upcoming','ongoing','completed'],true),
            404
        );

        $tour->load([
            'participants'=>fn($q)=>$q
                ->whereIn('status',['confirmed','attended'])
                ->with('member.user:id,name')
                ->orderBy('id'),
            'expenses'=>fn($q)=>$q
                ->where('status','posted')
                ->select([
                    'id',
                    'tour_id',
                    'category',
                    'amount',
                    'expense_date',
                    'description',
                ])
                ->latest('expense_date'),
        ]);

        $actual=round(
            (float)$tour->expenses->sum('amount'),
            2
        );

        return response()->json([
            'success'=>true,
            'data'=>[
                'id'=>$tour->id,
                'tour_no'=>$tour->tour_no,
                'title'=>$tour->title,
                'destination'=>$tour->destination,
                'description'=>$tour->description,
                'start_date'=>$tour->start_date,
                'end_date'=>$tour->end_date,
                'budget_amount'=>(float)$tour->budget_amount,
                'actual_expense'=>$actual,
                'budget_variance'=>round(
                    (float)$tour->budget_amount-$actual,
                    2
                ),
                'status'=>$tour->status,
                'participants'=>$tour->participants->map(fn($participant)=>[
                    'id'=>$participant->id,
                    'member_code'=>$participant->member?->member_code,
                    'name'=>$participant->member?->user?->name,
                    'status'=>$participant->status,
                ])->values(),
                'expenses'=>$tour->expenses->map(fn($expense)=>[
                    'id'=>$expense->id,
                    'category'=>$expense->category,
                    'amount'=>(float)$expense->amount,
                    'expense_date'=>$expense->expense_date,
                    'description'=>$expense->description,
                ])->values(),
            ],
        ]);
    }

    public function summary()
    {
        $base=Tour::query()
            ->whereIn('status',['approved','upcoming','ongoing','completed']);

        $total=(clone $base)->count();
        $upcoming=(clone $base)->where('status','upcoming')->count();
        $completed=(clone $base)->where('status','completed')->count();

        $budget=round(
            (float)(clone $base)->sum('budget_amount'),
            2
        );

        $actual=round(
            (float)\App\Models\TourExpense::query()
                ->where('status','posted')
                ->whereHas('tour',fn($q)=>$q->whereIn(
                    'status',
                    ['approved','upcoming','ongoing','completed']
                ))
                ->sum('amount'),
            2
        );

        return response()->json([
            'success'=>true,
            'data'=>[
                'total'=>$total,
                'upcoming'=>$upcoming,
                'completed'=>$completed,
                'total_budget'=>$budget,
                'actual_expense'=>$actual,
            ],
        ]);
    }
}