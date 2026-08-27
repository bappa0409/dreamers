<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\MailCampaign;
use App\Models\MailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCampaignMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public int $recipientId,
        public string $subject,
        public string $body,
    ) {}

    public function handle(): void
    {
        // Protect against two workers processing the same recipient at the same
        // time (for example, duplicate queue delivery). A worker that cannot
        // acquire the lock simply exits because the lock owner is already
        // responsible for this recipient.
        $lock = Cache::lock(
            "mail-campaign-recipient:{$this->recipientId}",
            120
        );

        if (!$lock->get()) {
            return;
        }

        try {
            $recipient = MailRecipient::find($this->recipientId);

            if (!$recipient || $recipient->status !== 'pending') {
                return;
            }

            Mail::to($recipient->email)->send(
                new CampaignMail(
                    subject: $this->subject,
                    body: $this->body,
                )
            );

            // Only increment once if this job changes pending -> sent.
            $updated = MailRecipient::query()
                ->whereKey($recipient->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'error_message' => null,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                MailCampaign::whereKey($recipient->mail_campaign_id)
                    ->increment('sent_count');
            }

            $this->finalizeCampaignIfFinished((int) $recipient->mail_campaign_id);
        } finally {
            $lock->release();
        }
    }

    public function failed(Throwable $e): void
    {
        $recipient = MailRecipient::find($this->recipientId);

        if (!$recipient) {
            return;
        }

        $updated = MailRecipient::query()
            ->whereKey($recipient->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
                'updated_at' => now(),
            ]);

        if ($updated) {
            MailCampaign::whereKey($recipient->mail_campaign_id)
                ->increment('failed_count');
        }

        $this->finalizeCampaignIfFinished((int) $recipient->mail_campaign_id);
    }

    protected function finalizeCampaignIfFinished(int $campaignId): void
    {
        $hasPending = MailRecipient::query()
            ->where('mail_campaign_id', $campaignId)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return;
        }

        $campaign = MailCampaign::find($campaignId);

        if (!$campaign || $campaign->status !== 'sending') {
            return;
        }

        $campaign->update([
            'status' => $campaign->failed_count > 0 ? 'failed' : 'completed',
            'sent_at' => now(),
        ]);
    }
}
