<?php

namespace App\Mail;

use App\Models\Business;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to every active super-admin contact right after a new business
 * finishes registering (AuthService::register()) — see
 * NewBusinessRegisteredJob for the fan-out. One business registration ->
 * one send per contact, each a separate queued email.
 */
class NewBusinessRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Business $business, public User $owner)
    {
    }

    public function build()
    {
        return $this->subject('New business registered on Bizaa: ' . $this->business->business_name)
            ->view('emails.new-business-registered');
    }
}
