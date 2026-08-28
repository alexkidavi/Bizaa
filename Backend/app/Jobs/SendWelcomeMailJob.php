<?php

namespace App\Jobs;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Wraps sending the registration welcome email with explicit logging at
 * every stage — dispatched from AuthService::register() instead of a bare
 * Mail::to()->queue() call. A "the job ran and reported success" log line
 * is the only way to tell "nothing sent because no queue worker is
 * running" apart from "sent, but to a dead-end log-driver mailer, or
 * blocked/spam-filtered downstream of us" — both look identical from the
 * registration response alone, so the diagnosis has to happen here.
 */
class SendWelcomeMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 300];

    public function __construct(protected User $user, protected string $businessName)
    {
    }

    public function handle(): void
    {
        $mailer = config('mail.default');

        Log::info('WelcomeMail: sending', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'business' => $this->businessName,
            'mailer' => $mailer,
        ]);

        try {
            Mail::to($this->user->email)->send(new WelcomeMail($this->user, $this->businessName));

            Log::info('WelcomeMail: sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'mailer' => $mailer,
                'note' => $mailer === 'log'
                    ? 'MAIL_MAILER is "log" — this did NOT deliver a real email, it only wrote the message to storage/logs/laravel.log. Set MAIL_MAILER to a real driver (smtp, ses, etc.) in .env to actually send.'
                    : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('WelcomeMail: send failed', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'mailer' => $mailer,
                'error' => $e->getMessage(),
            ]);

            throw $e; // let the queue's retry/backoff handle it
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('WelcomeMail: gave up after all retries', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $e->getMessage(),
        ]);
    }
}
