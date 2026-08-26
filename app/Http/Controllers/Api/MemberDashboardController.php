<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Poll;
use App\Models\PollVote;
use App\Services\MemberDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberDashboardController extends Controller
{
    public function __construct(
        protected MemberDashboardService $memberDashboardService
    ){}

    public function index(Request $request)
    {
        $member=$this->activeMember($request);

        return response()->json([
            'success'=>true,
            'data'=>[
                'member'=>$member->load([
                    'user:id,name,email,mobile,language,is_active',
                    'subscriptions.plan',
                    'shares',
                ]),
                'summary'=>$this->memberDashboardService->summary($member),
                'active_polls'=>$this->activePollQuery()->count(),
                'visible_notices'=>Notice::query()
                    ->visible()
                    ->count(),
            ],
        ]);
    }

    public function profile(Request $request)
    {
        $member=$this->activeMember($request);

        return response()->json([
            'success'=>true,
            'data'=>[
                'member'=>$member->load([
                    'user:id,name,email,mobile,language,is_active',
                ]),
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $member=$this->activeMember($request);

        $validated=$request->validate([
            'phone'=>'nullable|string|max:30',
            'alternate_phone'=>'nullable|string|max:30',
            'date_of_birth'=>'nullable|date|before:today',
            'gender'=>'nullable|in:male,female,other',
            'address'=>'nullable|string|max:1000',
            'city'=>'nullable|string|max:100',
            'district'=>'nullable|string|max:100',
            'profile_photo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_profile_photo'=>'nullable|boolean',
        ]);

        $oldPhoto=$member->profile_photo;
        $newPhoto=null;

        try{
            if($request->boolean('remove_profile_photo')){
                $validated['profile_photo']=null;
            }elseif($request->hasFile('profile_photo')){
                $newPhoto=$request->file('profile_photo')->store(
                    'members/profile-photos',
                    'public'
                );

                $validated['profile_photo']=$newPhoto;
            }else{
                unset($validated['profile_photo']);
            }

            unset($validated['remove_profile_photo']);

            $member->update($validated);

            if(
                ($newPhoto||$request->boolean('remove_profile_photo'))&&
                $oldPhoto&&
                Storage::disk('public')->exists($oldPhoto)
            ){
                Storage::disk('public')->delete($oldPhoto);
            }

            return response()->json([
                'success'=>true,
                'message'=>'Profile updated successfully.',
                'data'=>[
                    'member'=>$member->fresh([
                        'user:id,name,email,mobile,language,is_active',
                    ]),
                ],
            ]);

        }catch(\Throwable $e){
            if(
                $newPhoto&&
                Storage::disk('public')->exists($newPhoto)
            ){
                Storage::disk('public')->delete($newPhoto);
            }

            throw $e;
        }
    }

    public function polls(Request $request)
    {
        $member=$this->activeMember($request);

        $validated=$request->validate([
            'page'=>'nullable|integer|min:1',
            'per_page'=>'nullable|integer|min:5|max:30',
            'state'=>'nullable|in:voted,available',
            'history_page'=>'nullable|integer|min:1',
            'history_per_page'=>'nullable|integer|min:5|max:30',
        ]);

        $perPage=min(
            max((int)($validated['per_page']??10),5),
            30
        );

        $historyPerPage=min(
            max((int)($validated['history_per_page']??10),5),
            30
        );

        $state=$validated['state']??null;

        $activeBase=$this->activePollQuery();

        $activeCount=(clone $activeBase)->count();

        $votedActive=(clone $activeBase)
            ->whereHas(
                'votes',
                fn($q)=>$q->where('member_id',$member->id)
            )
            ->count();

        $availableActive=$activeCount-$votedActive;

        $pollQuery=$this->activePollQuery()
            ->with([
                'options',
                'votes'=>fn($q)=>$q->where(
                    'member_id',
                    $member->id
                ),
            ])
            ->latest('id');

        if($state==='voted'){
            $pollQuery->whereHas(
                'votes',
                fn($q)=>$q->where('member_id',$member->id)
            );
        }elseif($state==='available'){
            $pollQuery->whereDoesntHave(
                'votes',
                fn($q)=>$q->where('member_id',$member->id)
            );
        }

        $activePolls=$pollQuery->paginate(
            $perPage,
            ['*'],
            'page',
            (int)($validated['page']??1)
        );

        $activePolls->setCollection(
            $activePolls->getCollection()
                ->map(function(Poll $poll){
                    $myVote=$poll->votes->first();

                    return [
                        'id'=>$poll->id,
                        'title'=>$poll->title,
                        'description'=>$poll->description,
                        'start_at'=>$poll->start_at,
                        'end_at'=>$poll->end_at,
                        'options'=>$poll->options,
                        'has_voted'=>(bool)$myVote,
                        'my_vote_option_id'=>$myVote?->poll_option_id,
                    ];
                })
                ->values()
        );

        $votingHistory=PollVote::query()
            ->where('member_id',$member->id)
            ->with([
                'poll:id,title',
                'pollOption:id,option_text',
            ])
            ->latest('id')
            ->paginate(
                $historyPerPage,
                ['*'],
                'history_page',
                (int)($validated['history_page']??1)
            );

        $votingHistory->setCollection(
            $votingHistory->getCollection()
                ->map(fn(PollVote $vote)=>[
                    'poll_title'=>$vote->poll?->title,
                    'option_text'=>$vote->pollOption?->option_text,
                    'created_at'=>$vote->created_at,
                ])
                ->values()
        );

        return response()->json([
            'success'=>true,
            'data'=>[
                'active_polls'=>$activePolls,
                'voting_history'=>$votingHistory,
                'summary'=>[
                    'active_polls'=>$activeCount,
                    'available_active'=>$availableActive,
                    'voted_active'=>$votedActive,
                    'total_votes'=>PollVote::query()
                        ->where('member_id',$member->id)
                        ->count(),
                ],
            ],
        ]);
    }

    public function notices(Request $request)
    {
        $this->activeMember($request);

        $validated=$request->validate([
            'page'=>'nullable|integer|min:1',
            'per_page'=>'nullable|integer|min:5|max:30',
            'type'=>'nullable|in:notice,announcement,event,urgent',
            'priority'=>'nullable|in:low,normal,high,urgent',
        ]);

        $perPage=min(
            max((int)($validated['per_page']??20),5),
            30
        );

        /*
        |--------------------------------------------------------------------------
        | Global summary
        |--------------------------------------------------------------------------
        |
        | Summary intentionally ignores the current type/priority filter.
        | This keeps the four summary cards useful while browsing a filtered
        | page and avoids calculating counts from only the current page.
        |
        */
        $summary=Notice::query()
            ->visible()
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN type='announcement' THEN 1 ELSE 0 END)
                    AS announcements,
                SUM(CASE WHEN type='event' THEN 1 ELSE 0 END)
                    AS events,
                SUM(
                    CASE
                        WHEN priority IN ('high','urgent') THEN 1
                        ELSE 0
                    END
                ) AS important
            ")
            ->first();

        $query=Notice::query()
            ->visible()
            ->select([
                'id',
                'title',
                'content',
                'type',
                'priority',
                'attachment',
                'publish_at',
                'expires_at',
                'created_by',
                'created_at',
            ])
            ->when(
                !empty($validated['type']),
                fn($q)=>$q->where('type',$validated['type'])
            )
            ->when(
                !empty($validated['priority']),
                fn($q)=>$q->where(
                    'priority',
                    $validated['priority']
                )
            )
            ->with('creator:id,name,email')
            ->latest('id');

        $notices=$query
            ->paginate(
                $perPage,
                ['*'],
                'page',
                (int)($validated['page']??1)
            )
            ->withQueryString();

        return response()->json([
            'success'=>true,
            'data'=>[
                'notices'=>$notices,
                'summary'=>[
                    'total'=>(int)($summary->total??0),
                    'announcements'=>(int)($summary->announcements??0),
                    'events'=>(int)($summary->events??0),
                    'important'=>(int)($summary->important??0),
                ],
            ],
        ]);
    }

    protected function activeMember(Request $request)
    {
        $member=$request->user()->member;

        abort_unless(
            $member&&$member->status==='active',
            403,
            'Active membership is required.'
        );

        return $member;
    }

    protected function activePollQuery()
    {
        return Poll::query()
            ->where('is_active',true)
            ->where(function($q){
                $q->whereNull('start_at')
                    ->orWhere(
                        'start_at',
                        '<=',
                        now()
                    );
            })
            ->where(function($q){
                $q->whereNull('end_at')
                    ->orWhere(
                        'end_at',
                        '>=',
                        now()
                    );
            });
    }
}