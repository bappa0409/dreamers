<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\MailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        $recipient = MailRecipient::find($this->recipientId);

        if (!$recipient || $recipient->status === 'sent') {
            return;
        }

        Mail::to($recipient->email)->send(
            new CampaignMail(
                subject: $this->subject,
                body: $this->body,
            )
        );

        $recipient->update([
            'status'        => 'sent',
            'sent_at'       => now(),
            'error_message' => null,
        ]);
    }

    public function failed(Throwable $e): void
    {
        MailRecipient::whereKey($this->recipientId)->update([
            'status'        => 'failed',
            'error_message' => mb_substr($e->getMessage(), 0, 2000),
        ]);
    }
}