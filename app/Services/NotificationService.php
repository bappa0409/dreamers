<?php

namespace App\Services;

use App\Models\NotificationCampaign;
use App\Models\User;
use App\Notifications\AssociationNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class NotificationService
{
    public function send(array $data,int $senderId): NotificationCampaign
    {
        $campaign=NotificationCampaign::create([
            'title'=>$data['title'],
            'message'=>$data['message'],
            'type'=>$data['type']??'info',
            'audience_type'=>$data['audience_type'],
            'audience_data'=>$this->audienceData($data),
            'action_url'=>$data['action_url']??null,
            'status'=>'sending',
            'sent_by'=>$senderId,
        ]);

        try{
            $query=$this->recipientQuery(
                $data['audience_type'],
                $data
            );

            $recipientCount=0;

            $query->select('users.*')
                ->distinct()
                ->orderBy('users.id')
                ->chunkById(200,function($users)use($campaign,&$recipientCount){
                    Notification::send(
                        $users,
                        new AssociationNotification($campaign)
                    );

                    foreach($users as $user){
                        $this->forgetUserCache($user->id);
                    }

                    $recipientCount+=$users->count();
                },'users.id','id');

            if($recipientCount===0){
                throw ValidationException::withMessages([
                    'audience_type'=>[
                        'No matching notification recipients were found.'
                    ]
                ]);
            }

            $campaign->update([
                'status'=>'sent',
                'recipients_count'=>$recipientCount,
                'sent_at'=>now(),
                'error_message'=>null,
            ]);

            $this->forgetCampaignCaches();

            return $campaign->fresh('sender:id,name,email');

        }catch(\Throwable $e){
            $campaign->update([
                'status'=>'failed',
                'error_message'=>mb_substr($e->getMessage(),0,2000),
            ]);

            $this->forgetCampaignCaches();

            throw $e;
        }
    }

    public function sendSystem(array $data): void
{
    $query=$this->recipientQuery(
        $data['audience_type'],
        $data
    );

    $campaign=NotificationCampaign::create([
        'title'=>$data['title'],
        'message'=>$data['message'],
        'type'=>$data['type']??'info',
        'audience_type'=>$data['audience_type'],
        'audience_data'=>$this->audienceData($data),
        'action_url'=>$data['action_url']??null,
        'status'=>'sending',
        'sent_by'=>$data['sent_by']??auth()->id(),
    ]);

    $recipientCount=0;

    $query->select('users.*')
        ->distinct()
        ->orderBy('users.id')
        ->chunkById(200,function($users)use($campaign,&$recipientCount){
            Notification::send(
                $users,
                new AssociationNotification($campaign)
            );

            foreach($users as $user){
                $this->forgetUserCache($user->id);
            }

            $recipientCount+=$users->count();
        },'users.id','id');

    $campaign->update([
        'status'=>'sent',
        'recipients_count'=>$recipientCount,
        'sent_at'=>now(),
        'error_message'=>null,
    ]);

    $this->forgetCampaignCaches();
}

    protected function recipientQuery(string $audienceType,array $data): Builder
    {
        return match($audienceType){
            'all_active_members'=>User::query()
                ->where('is_active',true)
                ->whereHas('member',fn($q)=>$q->where('status','active')),

            'role'=>User::query()
                ->where('is_active',true)
                ->whereHas('roles',fn($q)=>$q->where('roles.id',$data['role_id'])),

            'users'=>User::query()
                ->where('is_active',true)
                ->whereIn('id',$data['user_ids']),

            default=>throw ValidationException::withMessages([
                'audience_type'=>['Invalid notification audience.']
            ])
        };
    }

    protected function audienceData(array $data): array
    {
        return match($data['audience_type']){
            'role'=>[
                'role_id'=>(int)$data['role_id']
            ],
            'users'=>[
                'user_ids'=>array_values(
                    array_unique(
                        array_map('intval',$data['user_ids'])
                    )
                )
            ],
            default=>[]
        };
    }

    public function unreadCount(User $user): int
    {
        return Cache::remember(
            "notifications:user:{$user->id}:unread",
            now()->addMinutes(5),
            fn()=>$user->unreadNotifications()->count()
        );
    }

    public function markRead(User $user,string $notificationId): void
    {
        $notification=$user->notifications()
            ->where('id',$notificationId)
            ->firstOrFail();

        if(!$notification->read_at){
            $notification->markAsRead();
        }

        $this->forgetUserCache($user->id);
    }

    public function markAllRead(User $user): void
    {
        $user->unreadNotifications()->update([
            'read_at'=>now()
        ]);

        $this->forgetUserCache($user->id);
    }

    public function delete(User $user,string $notificationId): void
    {
        $notification=$user->notifications()
            ->where('id',$notificationId)
            ->firstOrFail();

        $notification->delete();

        $this->forgetUserCache($user->id);
    }

    public function forgetUserCache(int $userId): void
    {
        Cache::forget(
            "notifications:user:{$userId}:unread"
        );
    }

    public function forgetCampaignCaches(): void
    {
        Cache::forget('notifications:campaigns:statistics');
    }
}