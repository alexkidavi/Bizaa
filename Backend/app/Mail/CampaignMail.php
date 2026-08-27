<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $campaignSubject;
    public string $body;

    public function __construct(string $subject, string $body)
    {
        $this->campaignSubject = $subject;
        $this->body = $body;
    }

    public function build()
    {
        return $this->subject($this->campaignSubject)
            ->view('emails.campaign');
    }
}
