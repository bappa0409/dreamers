<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\CommitteePosition;
use App\Models\CommitteeTerm;
use App\Models\Election;
use App\Models\ElectionCandidate;
use App\Models\Member;
use App\Services\CommitteeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class CommitteeController extends Controller
{
    public function __construct(protected CommitteeService $service){}

    public function index(Request $request)
    {
        $data=$request->validate([
            'search'=>'nullable|string|max:100',
            'status'=>'nullable|in:active,inactive',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=Committee::query()
            ->withCount('terms')
            ->with(['activeTerm'=>fn($q)=>$q->withCount('activeMembers')]);

        if($search=trim($data['search']??'')){
            $query->where('name','like',"%{$search}%");
        }

        if(isset($data['status'])){
            $query->where('is_active',$data['status']==='active');
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->orderByDesc('id')->paginate($data['per_page']??15)
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>Cache::remember('committee:statistics',now()->addMinutes(5),fn()=>[
                'committees'=>Committee::count(),
                'active_terms'=>CommitteeTerm::where('status','active')->count(),
                'active_members'=>CommitteeMember::where('status','active')->count(),
                'elections'=>Election::count()
            ])
        ]);
    }

    public function options(Request $request)
    {
        $search=trim((string)$request->query('member_search',''));

        $members=Member::query()
            ->select(['id','user_id','member_code'])
            ->with('user:id,name')
            ->where('status','active')
            ->when($search,function($q)use($search){
                $q->where(function($x)use($search){
                    $x->where('member_code','like',"%{$search}%")
                        ->orWhereHas('user',fn($u)=>$u->where('name','like',"%{$search}%"));
                });
            })
            ->limit(30)
            ->get();

        return response()->json([
            'success'=>true,
            'data'=>[
                'positions'=>CommitteePosition::where('is_active',true)
                    ->orderBy('sort_order')->orderBy('name')->get(),
                'members'=>$members
            ]
        ]);
    }

    public function current()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->service->currentCommittee()
        ]);
    }

    public function show(Committee $committee)
    {
        return response()->json([
            'success'=>true,
            'data'=>$committee->load([
                'terms'=>fn($q)=>$q->orderByDesc('start_date')
                    ->with([
                        'members'=>fn($m)=>$m->with(['position','member.user']),
                        'elections'=>fn($e)=>$e->with(['candidates.position','candidates.member.user'])
                    ])
            ])
        ]);
    }

    public function store(Request $request)
    {
        $data=$request->validate([
            'name'=>'required|string|max:150',
            'type'=>'required|string|max:50',
            'description'=>'nullable|string|max:2000',
            'is_active'=>'nullable|boolean'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Committee created successfully.',
            'data'=>$this->service->createCommittee($data,$request->user()->id)
        ],201);
    }

    public function update(Request $request,Committee $committee)
    {
        $data=$request->validate([
            'name'=>'sometimes|required|string|max:150',
            'type'=>'sometimes|required|string|max:50',
            'description'=>'nullable|string|max:2000',
            'is_active'=>'nullable|boolean'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Committee updated successfully.',
            'data'=>$this->service->updateCommittee($committee,$data)
        ]);
    }

    public function storePosition(Request $request)
    {
        $data=$request->validate([
            'code'=>'required|string|max:60|alpha_dash|unique:committee_positions,code',
            'name'=>'required|string|max:100',
            'description'=>'nullable|string|max:1000',
            'is_exclusive'=>'nullable|boolean',
            'is_active'=>'nullable|boolean',
            'sort_order'=>'nullable|integer|min:0|max:999'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Position created successfully.',
            'data'=>$this->service->createPosition($data)
        ],201);
    }

    public function updatePosition(Request $request,CommitteePosition $position)
    {
        $data=$request->validate([
            'code'=>[
                'sometimes','required','string','max:60','alpha_dash',
                Rule::unique('committee_positions','code')->ignore($position->id)
            ],
            'name'=>'sometimes|required|string|max:100',
            'description'=>'nullable|string|max:1000',
            'is_exclusive'=>'nullable|boolean',
            'is_active'=>'nullable|boolean',
            'sort_order'=>'nullable|integer|min:0|max:999'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Position updated successfully.',
            'data'=>$this->service->updatePosition($position,$data)
        ]);
    }

    public function storeTerm(Request $request,Committee $committee)
    {
        $data=$request->validate([
            'name'=>'required|string|max:100',
            'start_date'=>'required|date',
            'end_date'=>'required|date|after_or_equal:start_date',
            'notes'=>'nullable|string|max:2000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Committee term created successfully.',
            'data'=>$this->service->createTerm($committee,$data,$request->user()->id)
        ],201);
    }

    public function activateTerm(CommitteeTerm $term)
    {
        return response()->json([
            'success'=>true,
            'message'=>'Committee term activated.',
            'data'=>$this->service->activateTerm($term)
        ]);
    }

    public function completeTerm(CommitteeTerm $term)
    {
        return response()->json([
            'success'=>true,
            'message'=>'Committee term completed.',
            'data'=>$this->service->completeTerm($term)
        ]);
    }

    public function assignMember(Request $request,CommitteeTerm $term)
    {
        $data=$request->validate([
            'member_id'=>'required|integer|exists:members,id',
            'committee_position_id'=>'required|integer|exists:committee_positions,id',
            'appointment_method'=>'required|in:appointed,elected,replacement',
            'start_date'=>'nullable|date',
            'notes'=>'nullable|string|max:2000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Committee member assigned successfully.',
            'data'=>$this->service->assignMember($term,$data,$request->user()->id)
        ],201);
    }

    public function endMembership(Request $request,CommitteeMember $membership)
    {
        $data=$request->validate([
            'status'=>'required|in:resigned,removed',
            'notes'=>'nullable|string|max:2000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Committee membership updated.',
            'data'=>$this->service->endMembership(
                $membership,$data['status'],$data['notes']??null
            )
        ]);
    }

    public function storeElection(Request $request,CommitteeTerm $term)
    {
        $data=$request->validate([
            'title'=>'required|string|max:150',
            'election_date'=>'required|date',
            'notes'=>'nullable|string|max:2000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Election created successfully.',
            'data'=>$this->service->createElection($term,$data,$request->user()->id)
        ],201);
    }

    public function electionStatus(Request $request,Election $election)
    {
        $data=$request->validate([
            'status'=>'required|in:nomination_open,voting,completed,cancelled'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Election status updated.',
            'data'=>$this->service->changeElectionStatus($election,$data['status'])
        ]);
    }

    public function addCandidate(Request $request,Election $election)
    {
        $data=$request->validate([
            'member_id'=>'required|integer|exists:members,id',
            'committee_position_id'=>'required|integer|exists:committee_positions,id',
            'notes'=>'nullable|string|max:1000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Candidate added successfully.',
            'data'=>$this->service->addCandidate($election,$data)
        ],201);
    }

    public function result(Request $request,ElectionCandidate $candidate)
    {
        $data=$request->validate([
            'votes'=>'required|integer|min:0',
            'elected'=>'required|boolean'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Election result saved successfully.',
            'data'=>$this->service->setResult(
                $candidate,$data['votes'],$data['elected'],$request->user()->id
            )
        ]);
    }
}