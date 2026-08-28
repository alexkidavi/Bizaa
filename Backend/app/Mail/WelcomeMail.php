<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent once, right after a new business finishes registering
 * (AuthService::register()). Unlike the old UserOnboardingMail (built for
 * a staff-member-created-by-an-admin flow with a system-generated
 * temporary password), a self-registering user already chose their own
 * password at signup — this email never carries a password at all.
 */
class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $businessName)
    {
    }

    public function build()
    {
        return $this->subject('Welcome to Bizaa, ' . $this->user->name . '!')
            ->view('emails.welcome');
    }
}
