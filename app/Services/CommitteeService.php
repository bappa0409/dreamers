<?php

namespace App\Services;

use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\CommitteePosition;
use App\Models\CommitteeTerm;
use App\Models\Election;
use App\Models\ElectionCandidate;
use App\Models\Member;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommitteeService
{
    public function __construct(protected NotificationService $notifications){}

    public function createCommittee(array $data,int $userId): Committee
    {
        return DB::transaction(function()use($data,$userId){
            $committee=Committee::create([
                'name'=>$data['name'],
                'type'=>$data['type']??'executive',
                'description'=>$data['description']??null,
                'is_active'=>$data['is_active']??true,
                'created_by'=>$userId
            ]);
            $this->forgetCache();
            return $committee;
        });
    }

    public function updateCommittee(Committee $committee,array $data): Committee
    {
        $committee->update([
            'name'=>$data['name']??$committee->name,
            'type'=>$data['type']??$committee->type,
            'description'=>$data['description']??$committee->description,
            'is_active'=>$data['is_active']??$committee->is_active
        ]);
        $this->forgetCache();
        return $committee->fresh();
    }

    public function createPosition(array $data): CommitteePosition
    {
        $position=CommitteePosition::create([
            'code'=>strtoupper($data['code']),
            'name'=>$data['name'],
            'description'=>$data['description']??null,
            'is_exclusive'=>$data['is_exclusive']??true,
            'is_active'=>$data['is_active']??true,
            'sort_order'=>$data['sort_order']??0
        ]);
        $this->forgetCache();
        return $position;
    }

    public function updatePosition(CommitteePosition $position,array $data): CommitteePosition
    {
        $position->update([
            'code'=>isset($data['code'])?strtoupper($data['code']):$position->code,
            'name'=>$data['name']??$position->name,
            'description'=>$data['description']??$position->description,
            'is_exclusive'=>$data['is_exclusive']??$position->is_exclusive,
            'is_active'=>$data['is_active']??$position->is_active,
            'sort_order'=>$data['sort_order']??$position->sort_order
        ]);
        $this->forgetCache();
        return $position->fresh();
    }

    public function createTerm(Committee $committee,array $data,int $userId): CommitteeTerm
    {
        if($data['end_date']<$data['start_date']){
            throw ValidationException::withMessages([
                'end_date'=>['End date must be on or after start date.']
            ]);
        }

        return DB::transaction(function()use($committee,$data,$userId){
            $overlap=$committee->terms()
                ->where('status','!=','cancelled')
                ->whereDate('start_date','<=',$data['end_date'])
                ->whereDate('end_date','>=',$data['start_date'])
                ->exists();

            if($overlap){
                throw ValidationException::withMessages([
                    'start_date'=>['This committee already has an overlapping term.']
                ]);
            }

            $term=$committee->terms()->create([
                'name'=>$data['name'],
                'start_date'=>$data['start_date'],
                'end_date'=>$data['end_date'],
                'status'=>'draft',
                'notes'=>$data['notes']??null,
                'created_by'=>$userId
            ]);

            $this->forgetCache();
            return $term;
        });
    }

    public function activateTerm(CommitteeTerm $term): CommitteeTerm
    {
        return DB::transaction(function()use($term){
            $term=CommitteeTerm::query()->lockForUpdate()->findOrFail($term->id);

            if($term->status!=='draft'){
                throw ValidationException::withMessages([
                    'term'=>['Only draft term can be activated.']
                ]);
            }

            $hasActive=CommitteeTerm::query()
                ->where('committee_id',$term->committee_id)
                ->where('status','active')
                ->whereKeyNot($term->id)
                ->lockForUpdate()
                ->exists();

            if($hasActive){
                throw ValidationException::withMessages([
                    'term'=>['This committee already has an active term.']
                ]);
            }

            $term->update(['status'=>'active']);
            $this->forgetCache();

            return $term->fresh('committee');
        });
    }

    public function completeTerm(CommitteeTerm $term): CommitteeTerm
    {
        return DB::transaction(function()use($term){
            $term=CommitteeTerm::query()->lockForUpdate()->findOrFail($term->id);

            if($term->status!=='active'){
                throw ValidationException::withMessages([
                    'term'=>['Only active term can be completed.']
                ]);
            }

            $term->members()
                ->where('status','active')
                ->update([
                    'status'=>'completed',
                    'end_date'=>now()->toDateString()
                ]);

            $term->update(['status'=>'completed']);
            $this->forgetCache();

            return $term->fresh();
        });
    }

    public function assignMember(CommitteeTerm $term,array $data,int $userId): CommitteeMember
    {
        return DB::transaction(function()use($term,$data,$userId){
            $term=CommitteeTerm::query()->lockForUpdate()->findOrFail($term->id);

            if($term->status!=='active'){
                throw ValidationException::withMessages([
                    'term'=>['Members can only be assigned to an active committee term.']
                ]);
            }

            $member=Member::query()
                ->with('user:id,name,is_active')
                ->whereKey($data['member_id'])
                ->where('status','active')
                ->first();

            if(!$member){
                throw ValidationException::withMessages([
                    'member_id'=>['Only active association members can hold committee positions.']
                ]);
            }

            $position=CommitteePosition::query()
                ->whereKey($data['committee_position_id'])
                ->where('is_active',true)
                ->firstOrFail();

            $alreadyInPosition=CommitteeMember::query()
                ->where('committee_term_id',$term->id)
                ->where('committee_position_id',$position->id)
                ->where('member_id',$member->id)
                ->where('status','active')
                ->exists();

            if($alreadyInPosition){
                throw ValidationException::withMessages([
                    'member_id'=>['This member already holds this position in the term.']
                ]);
            }

            if($position->is_exclusive){
                $occupied=CommitteeMember::query()
                    ->where('committee_term_id',$term->id)
                    ->where('committee_position_id',$position->id)
                    ->where('status','active')
                    ->lockForUpdate()
                    ->exists();

                if($occupied){
                    throw ValidationException::withMessages([
                        'committee_position_id'=>[
                            "{$position->name} is an exclusive position and already has an active member."
                        ]
                    ]);
                }
            }

            $assignment=CommitteeMember::create([
                'committee_term_id'=>$term->id,
                'committee_position_id'=>$position->id,
                'member_id'=>$member->id,
                'appointment_method'=>$data['appointment_method']??'appointed',
                'start_date'=>$data['start_date']??now()->toDateString(),
                'status'=>'active',
                'notes'=>$data['notes']??null,
                'created_by'=>$userId
            ]);

            if($member->user_id){
                $this->notifications->sendSystem([
                    'title'=>'Committee Position Assigned',
                    'message'=>"You have been assigned as {$position->name} for {$term->name}.",
                    'type'=>'success',
                    'audience_type'=>'users',
                    'user_ids'=>[$member->user_id],
                    'action_url'=>route('member.committee'),
                    'sent_by'=>$userId
                ]);
            }

            $this->forgetCache();

            return $assignment->load([
                'member.user:id,name,email',
                'position',
                'term.committee'
            ]);
        });
    }

    public function endMembership(CommitteeMember $assignment,string $status,?string $notes=null): CommitteeMember
    {
        if(!in_array($status,['resigned','removed'],true)){
            throw ValidationException::withMessages([
                'status'=>['Invalid committee membership status.']
            ]);
        }

        return DB::transaction(function()use($assignment,$status,$notes){
            $assignment=CommitteeMember::query()
                ->with(['member.user','position','term'])
                ->lockForUpdate()
                ->findOrFail($assignment->id);

            if($assignment->status!=='active'){
                throw ValidationException::withMessages([
                    'membership'=>['Only active committee membership can be ended.']
                ]);
            }

            $assignment->update([
                'status'=>$status,
                'end_date'=>now()->toDateString(),
                'notes'=>$notes??$assignment->notes
            ]);

            if($assignment->member?->user_id){
                $this->notifications->sendSystem([
                    'title'=>'Committee Membership Updated',
                    'message'=>"Your {$assignment->position->name} position has been marked as {$status}.",
                    'type'=>'info',
                    'audience_type'=>'users',
                    'user_ids'=>[$assignment->member->user_id],
                    'action_url'=>route('member.committee')
                ]);
            }

            $this->forgetCache();
            return $assignment->fresh(['member.user','position','term.committee']);
        });
    }

    public function createElection(CommitteeTerm $term,array $data,int $userId): Election
    {
        if($term->status==='cancelled'||$term->status==='completed'){
            throw ValidationException::withMessages([
                'term'=>['Election cannot be created for this term.']
            ]);
        }

        return Election::create([
            'committee_term_id'=>$term->id,
            'title'=>$data['title'],
            'election_date'=>$data['election_date'],
            'status'=>'draft',
            'notes'=>$data['notes']??null,
            'created_by'=>$userId
        ]);
    }

    public function changeElectionStatus(Election $election,string $status): Election
    {
        return DB::transaction(function()use($election,$status){
            $election=Election::query()->lockForUpdate()->findOrFail($election->id);

            $transitions=[
                'draft'=>['nomination_open','cancelled'],
                'nomination_open'=>['voting','cancelled'],
                'voting'=>['completed','cancelled'],
                'completed'=>[],
                'cancelled'=>[]
            ];

            if(!in_array($status,$transitions[$election->status]??[],true)){
                throw ValidationException::withMessages([
                    'status'=>["Invalid election transition: {$election->status} → {$status}."]
                ]);
            }

            if($status==='completed'&&!$election->candidates()->where('status','elected')->exists()){
                throw ValidationException::withMessages([
                    'status'=>['Mark election winners before completing the election.']
                ]);
            }

            $election->update(['status'=>$status]);
            $this->forgetCache();

            return $election->fresh();
        });
    }

    public function addCandidate(Election $election,array $data): ElectionCandidate
    {
        if(!in_array($election->status,['draft','nomination_open'],true)){
            throw ValidationException::withMessages([
                'election'=>['Candidates cannot be added at this stage.']
            ]);
        }

        Member::query()
            ->whereKey($data['member_id'])
            ->where('status','active')
            ->firstOrFail();

        CommitteePosition::query()
            ->whereKey($data['committee_position_id'])
            ->where('is_active',true)
            ->firstOrFail();

        return ElectionCandidate::create([
            'election_id'=>$election->id,
            'committee_position_id'=>$data['committee_position_id'],
            'member_id'=>$data['member_id'],
            'notes'=>$data['notes']??null
        ])->load(['member.user','position']);
    }

    public function setResult(ElectionCandidate $candidate,int $votes,bool $elected,int $userId): ElectionCandidate
    {
        return DB::transaction(function()use($candidate,$votes,$elected,$userId){
            $candidate=ElectionCandidate::query()
                ->with(['election.term','position','member.user'])
                ->lockForUpdate()
                ->findOrFail($candidate->id);

            if($candidate->election->status!=='voting'){
                throw ValidationException::withMessages([
                    'election'=>['Results can only be entered while election is in voting status.']
                ]);
            }

            if($elected&&$candidate->position->is_exclusive){
                $winnerExists=ElectionCandidate::query()
                    ->where('election_id',$candidate->election_id)
                    ->where('committee_position_id',$candidate->committee_position_id)
                    ->where('status','elected')
                    ->whereKeyNot($candidate->id)
                    ->lockForUpdate()
                    ->exists();

                if($winnerExists){
                    throw ValidationException::withMessages([
                        'elected'=>['This exclusive position already has an elected winner.']
                    ]);
                }
            }

            $candidate->update([
                'votes'=>$votes,
                'status'=>$elected?'elected':'not_elected'
            ]);

            if($elected){
                $this->assignMember($candidate->election->term,[
                    'member_id'=>$candidate->member_id,
                    'committee_position_id'=>$candidate->committee_position_id,
                    'appointment_method'=>'elected',
                    'start_date'=>$candidate->election->term->start_date->toDateString(),
                    'notes'=>"Elected through {$candidate->election->title}"
                ],$userId);
            }

            $this->forgetCache();
            return $candidate->fresh(['member.user','position']);
        });
    }

    public function currentCommittee()
    {
        return Cache::remember('committee:current',now()->addMinutes(5),function(){
            return CommitteeTerm::query()
                ->where('status','active')
                ->with([
                    'committee:id,name,type,description',
                    'activeMembers'=>fn($q)=>$q
                        ->with([
                            'position:id,code,name,sort_order',
                            'member:id,user_id,member_code,profile_photo',
                            'member.user:id,name'
                        ])
                        ->orderBy(
                            CommitteePosition::select('sort_order')
                                ->whereColumn('committee_positions.id','committee_members.committee_position_id')
                                ->limit(1)
                        )
                ])
                ->orderByDesc('start_date')
                ->get();
        });
    }

    public function forgetCache(): void
    {
        Cache::forget('committee:current');
        Cache::forget('committee:statistics');
    }
}