<?php

namespace App\Services;

use App\Models\MailCampaign;
use App\Models\MailRecipient;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\CampaignMail;

class MailCampaignService
{
    public function createCampaign(
        array $data,
        User $creator
    ): MailCampaign {

        return MailCampaign::create([
            'subject' => $data['subject'],
            'content' => $data['content'],
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);
    }

    public function addRecipient(
        MailCampaign $campaign,
        string $email,
        ?string $name = null,
        ?int $userId = null,
        ?int $memberId = null
    ): MailRecipient {

        return $campaign->recipients()->create([
            'email' => $email,
            'name' => $name,
            'user_id' => $userId,
            'member_id' => $memberId,
            'status' => 'pending',
        ]);
    }

    public function sendCampaign(
        MailCampaign $campaign
    ): void {

        $campaign->update([
            'status' => 'sending',
        ]);

        $sent = 0;
        $failed = 0;

        foreach ($campaign->recipients()->where(
            'status',
            'pending'
        )->get() as $recipient) {

            try {

                Mail::to($recipient->email)
                    ->send(
                        new CampaignMail($campaign)
                    );

                $recipient->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                $sent++;

            } catch (\Throwable $e) {

                $recipient->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                $failed++;
            }
        }

        $campaign->update([
            'status' => $failed > 0 && $sent === 0
                ? 'failed'
                : 'completed',

            'sent_at' => now(),

            'sent_count' => $sent,

            'failed_count' => $failed,
        ]);
    }
}