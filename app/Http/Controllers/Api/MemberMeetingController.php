<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MemberMeetingController extends Controller
{
    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:scheduled,ongoing,completed',
            'year'=>'nullable|integer|min:2000|max:2100',
            'per_page'=>'nullable|integer|min:5|max:50'
        ]);

        $query=$this->visibleMeetings($request)
            ->withCount([
                'attendees as attendee_count'=>fn($q)=>$q->where('status','present'),
                'decisions'
            ])
            ->withSum([
                'expenses as actual_expense'=>fn($q)=>$q->where('status','posted')
            ],'amount')
            ->latest('meeting_date')
            ->latest('id');

        if(!empty($validated['search'])){
            $search=trim($validated['search']);

            $query->where(function($q)use($search){
                $q->where('meeting_no','like',"%{$search}%")
                    ->orWhere('title','like',"%{$search}%")
                    ->orWhere('venue','like',"%{$search}%");
            });
        }

        if(!empty($validated['status'])){
            $query->where('status',$validated['status']);
        }

        if(!empty($validated['year'])){
            $query->whereYear('meeting_date',$validated['year']);
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate($validated['per_page']??12)
        ]);
    }

    public function show(Request $request,Meeting $meeting)
    {
        $allowed=$this->visibleMeetings($request)
            ->whereKey($meeting->id)
            ->exists();

        abort_unless($allowed,404);

        $meeting->load([
            'creator:id,name,email',
            'completer:id,name,email',

            'agendas'=>fn($q)=>
                $q->orderBy('sort_order')
                    ->orderBy('id'),

            'attendees'=>fn($q)=>
                $q->with([
                    'member:id,user_id,member_code,status',
                    'member.user:id,name,email,mobile',
                ])->latest('id'),

            'decisions'=>fn($q)=>
                $q->with([
                    'agenda:id,meeting_id,title',
                    'responsibleUser:id,name,email',
                ])->latest('id'),

            'expenses'=>fn($q)=>
                $q->with([
                    'expenseAccount:id,code,name,type,sub_type',
                    'paymentAccount:id,code,name,type,sub_type',
                    'creator:id,name,email',
                    'financeTransaction.entries.account',
                ])
                    ->latest('expense_date')
                    ->latest('id'),
        ]);

        $postedExpenses=$meeting->expenses
            ->where('status','posted');

        $actualExpense=round(
            (float)$postedExpenses->sum('amount'),
            2
        );

        $budget=round(
            (float)$meeting->budget_amount,
            2
        );

        $attendance=[
            'total'=>$meeting->attendees->count(),

            'invited'=>$meeting->attendees
                ->where('status','invited')
                ->count(),

            'present'=>$meeting->attendees
                ->where('status','present')
                ->count(),

            'absent'=>$meeting->attendees
                ->where('status','absent')
                ->count(),

            'excused'=>$meeting->attendees
                ->where('status','excused')
                ->count(),
        ];

        $decisions=[
            'total'=>$meeting->decisions->count(),

            'pending'=>$meeting->decisions
                ->where('status','pending')
                ->count(),

            'in_progress'=>$meeting->decisions
                ->where('status','in_progress')
                ->count(),

            'completed'=>$meeting->decisions
                ->where('status','completed')
                ->count(),

            'cancelled'=>$meeting->decisions
                ->where('status','cancelled')
                ->count(),
        ];

        return response()->json([
            'success'=>true,

            'data'=>array_merge(
                $meeting->toArray(),
                [
                    'actual_expense'=>$actualExpense,

                    'budget_variance'=>round(
                        $budget-$actualExpense,
                        2
                    ),

                    'attendance_summary'=>$attendance,

                    'decision_summary'=>$decisions,
                ]
            ),
        ]);
    }

    public function summary(Request $request)
    {
        $base=$this->visibleMeetings($request);

        return response()->json([
            'success'=>true,
            'data'=>[
                'total'=>(clone $base)->count(),
                'scheduled'=>(clone $base)
                    ->where('status','scheduled')
                    ->count(),
                'completed'=>(clone $base)
                    ->where('status','completed')
                    ->count()
            ]
        ]);
    }

    protected function visibleMeetings(Request $request): Builder
    {
        $memberId=$request->user()->member?->id;

        abort_unless($memberId,403,'Active member profile required.');

        return Meeting::query()
            ->whereIn('status',['scheduled','ongoing','completed'])
            ->where(function($query)use($memberId){
                $query->where('type','!=','executive')
                    ->orWhereHas(
                        'attendees',
                        fn($attendee)=>$attendee->where('member_id',$memberId)
                    );
            });
    }
}
