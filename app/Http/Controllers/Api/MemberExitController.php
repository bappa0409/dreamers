<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Member;
use App\Models\MemberExit;
use App\Services\MemberExitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MemberExitController extends Controller
{
    public function __construct(
        protected MemberExitService $service
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|string|max:50',
            'exit_type'=>'nullable|string|max:50',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=MemberExit::query()
            ->with([
                'member:id,user_id,member_code,status',
                'member.user:id,name,email',
            ])
            ->when(
                $validated['status']??null,
                fn($q,$status)=>
                    $q->where('status',$status)
            )
            ->when(
                $validated['exit_type']??null,
                fn($q,$type)=>
                    $q->where('exit_type',$type)
            )
            ->when(
                $validated['search']??null,
                function($q,$search){
                    $search=trim($search);

                    $q->where(function($q)use($search){
                        $q->where(
                            'exit_no',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'member',
                            function($m)use($search){
                                $m->where(
                                    'member_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'user',
                                    fn($u)=>$u->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                            }
                        );
                    });
                }
            )
            ->latest('id');

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                $validated['per_page']??15
            ),
        ]);
    }

    public function statistics(): array
    {
        return Cache::remember(
            'member-exits:statistics',
            now()->addMinutes(5),
            function(){
                $row=MemberExit::query()
                    ->selectRaw("
                        COUNT(*) total,

                        SUM(
                            status IN (
                                'submitted',
                                'under_review',
                                'liabilities_pending',
                                'ready_for_approval'
                            )
                        ) pending,

                        SUM(status='approved') approved,

                        SUM(status='settled') settled,

                        SUM(status='closed') closed,

                        SUM(status='rejected') rejected,

                        SUM(status='cancelled') cancelled,

                        SUM(exit_type='death') death
                    ")
                    ->first();

                return[
                    'total'=>(int)($row->total??0),
                    'pending'=>(int)($row->pending??0),
                    'approved'=>(int)($row->approved??0),
                    'settled'=>(int)($row->settled??0),
                    'closed'=>(int)($row->closed??0),
                    'rejected'=>(int)($row->rejected??0),
                    'cancelled'=>(int)($row->cancelled??0),
                    'death'=>(int)($row->death??0),
                ];
            }
        );
    }

    public function options(Request $request)
    {
        $search=trim(
            (string)$request->query(
                'member_search',
                ''
            )
        );

        return response()->json([
            'success'=>true,
            'data'=>[
                'members'=>Member::query()
                    ->select([
                        'id',
                        'user_id',
                        'member_code',
                        'status',
                    ])
                    ->with('user:id,name')
                    ->whereNotIn(
                        'status',
                        [
                            'rejected',
                            'exited',
                            'deceased',
                        ]
                    )
                    ->when(
                        $search,
                        function($q)use($search){
                            $q->where(function($q)use($search){
                                $q->where(
                                    'member_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'user',
                                    fn($u)=>$u->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                            });
                        }
                    )
                    ->limit(30)
                    ->get(),

                'accounts'=>Account::query()
                    ->where('is_active',true)
                    ->whereIn(
                        'sub_type',
                        ['cash','bank']
                    )
                    ->whereDoesntHave('children')
                    ->orderBy('code')
                    ->get([
                        'id',
                        'code',
                        'name',
                        'sub_type',
                    ]),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>'required|integer|exists:members,id',
            'exit_type'=>
                'required|in:resignation,termination,death,permanent_removal,other',

            'request_date'=>'nullable|date_format:Y-m-d',
            'proposed_exit_date'=>'nullable|date_format:Y-m-d',
            'reason'=>'required|string|max:5000',
        ]);

        $member=Member::findOrFail(
            $validated['member_id']
        );

        return response()->json([
            'success'=>true,
            'message'=>'Member exit process started.',
            'data'=>$this->service->initiate(
                $member,
                $validated,
                $request->user()->id
            ),
        ],201);
    }

    public function show(MemberExit $memberExit)
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->service->details(
                $memberExit
            ),
        ]);
    }

    public function review(
        Request $request,
        MemberExit $memberExit
    ){
        $validated=$request->validate([
            'review_note'=>'nullable|string|max:5000',
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Exit review started.',
            'data'=>$this->service->startReview(
                $memberExit,
                $validated['review_note']??null,
                $request->user()->id
            ),
        ]);
    }

    public function assess(
        Request $request,
        MemberExit $memberExit
    ){
        return response()->json([
            'success'=>true,
            'message'=>'Financial assessment refreshed.',
            'data'=>$this->service->assess(
                $memberExit,
                $request->user()->id
            ),
        ]);
    }

    public function approve(
        Request $request,
        MemberExit $memberExit
    ){
        return response()->json([
            'success'=>true,
            'message'=>'Exit request approved.',
            'data'=>$this->service->approve(
                $memberExit,
                $request->user()->id
            ),
        ]);
    }

    public function reject(
        Request $request,
        MemberExit $memberExit
    ){
        $validated=$request->validate([
            'rejection_reason'=>
                'required|string|max:5000',
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Exit request rejected.',
            'data'=>$this->service->reject(
                $memberExit,
                $validated['rejection_reason'],
                $request->user()->id
            ),
        ]);
    }

    public function cancel(
        Request $request,
        MemberExit $memberExit
    ){
        return response()->json([
            'success'=>true,
            'message'=>'Exit process cancelled.',
            'data'=>$this->service->cancel(
                $memberExit,
                $request->user()->id
            ),
        ]);
    }

    public function settle(
        Request $request,
        MemberExit $memberExit
    ){
        $validated=$request->validate([
            'payout_account_id'=>
                'required|integer|exists:accounts,id',

            'settlement_date'=>
                'required|date_format:Y-m-d',
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Exit settlement completed.',
            'data'=>$this->service->settle(
                $memberExit,
                $validated,
                $request->user()->id
            ),
        ]);
    }

    public function close(
        Request $request,
        MemberExit $memberExit
    ){
        return response()->json([
            'success'=>true,
            'message'=>'Membership permanently closed.',
            'data'=>$this->service->close(
                $memberExit,
                $request->user()->id
            ),
        ]);
    }
}