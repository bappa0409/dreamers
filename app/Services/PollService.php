<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PollService
{
    public function __construct(
        protected DashboardService $dashboardService
    ){}

    public function create(array $data,int $userId): Poll
    {
        return DB::transaction(function()use($data,$userId){
            $options=$data['options'];
            unset($data['options']);

            $poll=Poll::create([
                ...$data,
                'created_by'=>$userId,
                'is_active'=>$data['is_active']??true
            ]);

            foreach($options as $index=>$option){
                $poll->options()->create([
                    'option_text'=>$option,
                    'sort_order'=>$index
                ]);
            }

            $this->forgetCaches();

            return $poll;
        });
    }

    public function update(Poll $poll,array $data): Poll
    {
        return DB::transaction(function()use($poll,$data){
            $options=$data['options']??null;
            unset($data['options']);

            $poll->update($data);

            if($options!==null){
                if($poll->votes()->exists()){
                    throw ValidationException::withMessages([
                        'options'=>[
                            'Poll options cannot be changed after voting has started.'
                        ]
                    ]);
                }

                $poll->options()->delete();

                foreach($options as $index=>$option){
                    $poll->options()->create([
                        'option_text'=>$option,
                        'sort_order'=>$index
                    ]);
                }
            }

            $this->forgetCaches();

            return $poll->fresh('options');
        });
    }

    public function vote(
        Poll $poll,
        int $optionId,
        Member $member
    ): PollVote{
        return DB::transaction(function()use($poll,$optionId,$member){
            $poll=Poll::query()
                ->lockForUpdate()
                ->findOrFail($poll->id);

            if(!$poll->is_active){
                throw ValidationException::withMessages([
                    'poll'=>['This poll is inactive.']
                ]);
            }

            if(now()->lt($poll->start_at)||now()->gt($poll->end_at)){
                throw ValidationException::withMessages([
                    'poll'=>['Voting is not available at this time.']
                ]);
            }

            $option=$poll->options()
                ->whereKey($optionId)
                ->first();

            if(!$option){
                throw ValidationException::withMessages([
                    'poll_option_id'=>['Invalid poll option.']
                ]);
            }

            $alreadyVoted=PollVote::query()
                ->where('poll_id',$poll->id)
                ->where('member_id',$member->id)
                ->exists();

            if($alreadyVoted){
                throw ValidationException::withMessages([
                    'poll'=>['You have already voted in this poll.']
                ]);
            }

            $vote=PollVote::create([
                'poll_id'=>$poll->id,
                'poll_option_id'=>$option->id,
                'member_id'=>$member->id
            ]);

            $this->forgetCaches();

            return $vote;
        });
    }

    public function delete(Poll $poll): void
    {
        DB::transaction(function()use($poll){
            $poll->delete();
            $this->forgetCaches();
        });
    }

    public function results(Poll $poll): array
    {
        $options=$poll->options()
            ->withCount('votes')
            ->orderBy('sort_order')
            ->get();

        $total=$options->sum('votes_count');

        return [
            'poll'=>[
                'id'=>$poll->id,
                'title'=>$poll->title,
                'description'=>$poll->description,
                'start_at'=>$poll->start_at,
                'end_at'=>$poll->end_at,
                'is_active'=>$poll->is_active,
                'state'=>$poll->state
            ],
            'total_votes'=>$total,
            'results'=>$options->map(function($option)use($total){
                return [
                    'id'=>$option->id,
                    'option_text'=>$option->option_text,
                    'votes'=>(int)$option->votes_count,
                    'percentage'=>$total>0
                        ?round(($option->votes_count/$total)*100,2)
                        :0
                ];
            })->values()
        ];
    }

    public function statistics(): array
    {
        return Cache::remember(
            'polls:statistics',
            now()->addMinutes(5),
            function(){
                $now=now();

                $row=Poll::query()
                    ->selectRaw(
                        "COUNT(*) AS total,
                        SUM(CASE
                            WHEN is_active = 1
                                AND start_at <= ?
                                AND end_at >= ?
                            THEN 1 ELSE 0
                        END) AS active,
                        SUM(CASE
                            WHEN is_active = 1
                                AND start_at > ?
                            THEN 1 ELSE 0
                        END) AS upcoming,
                        SUM(CASE
                            WHEN is_active = 1
                                AND end_at < ?
                            THEN 1 ELSE 0
                        END) AS ended,
                        SUM(CASE
                            WHEN is_active = 0
                            THEN 1 ELSE 0
                        END) AS inactive",
                        [$now,$now,$now,$now]
                    )
                    ->first();

                return [
                    'total'=>(int)($row->total??0),
                    'active'=>(int)($row->active??0),
                    'upcoming'=>(int)($row->upcoming??0),
                    'ended'=>(int)($row->ended??0),
                    'inactive'=>(int)($row->inactive??0),
                    'total_votes'=>PollVote::query()->count()
                ];
            }
        );
    }

    public function forgetCaches(): void
    {
        Cache::forget('polls:statistics');
        Cache::forget('dashboard.polls.summary');
        $this->dashboardService->forgetDashboardCaches();
    }
}