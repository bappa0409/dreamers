<?php

namespace App\Notifications;

use App\Models\NotificationCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssociationNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected NotificationCampaign $campaign
    ){}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'campaign_id'=>$this->campaign->id,
            'title'=>$this->campaign->title,
            'message'=>$this->campaign->message,
            'type'=>$this->campaign->type,
            'action_url'=>$this->campaign->action_url,
            'sent_at'=>$this->campaign->sent_at?->toISOString()??now()->toISOString(),
        ];
    }
}