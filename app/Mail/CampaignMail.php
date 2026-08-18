<?php

namespace App\Mail;

use App\Models\MailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MailCampaign $campaign
    ) {
    }

    public function build()
    {
        return $this
            ->subject($this->campaign->subject)
            ->view('emails.campaign');
    }
}