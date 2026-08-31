<?php

namespace App\Jobs;

use App\Mail\NewBusinessRegisteredMail;
use App\Models\Business;
use App\Models\SuperAdminContact;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Notifies every active super-admin contact (see the /superadmin/contacts
 * CRUD) whenever a new business registers. One job per registration, one
 * email per contact inside it — the contact list is small (a handful of
 * people, not a bulk campaign), so this doesn't need the Bus::batch +
 * per-recipient-job pattern CRM campaigns use; a single try/catch per
 * contact is enough to stop one bad address from blocking the rest.
 */
class NewBusinessRegisteredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 300];

    public function __construct(protected Business $business, protected User $owner)
    {
    }

    public function handle(): void
    {
        $contacts = SuperAdminContact::active()->get();

        if ($contacts->isEmpty()) {
            Log::info('NewBusinessRegisteredMail: no active super-admin contacts configured, nothing to send', [
                'business_id' => $this->business->id,
            ]);
            return;
        }

        foreach ($contacts as $contact) {
            try {
                Mail::to($contact->email)->send(new NewBusinessRegisteredMail($this->business, $this->owner));

                Log::info('NewBusinessRegisteredMail: sent', [
                    'business_id' => $this->business->id,
                    'contact_email' => $contact->email,
                ]);
            } catch (\Throwable $e) {
                Log::error('NewBusinessRegisteredMail: send failed for one contact', [
                    'business_id' => $this->business->id,
                    'contact_email' => $contact->email,
                    'error' => $e->getMessage(),
                ]);
                // Keep going — one bad address on the contact list
                // shouldn't stop the rest of the team from being notified.
            }
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('NewBusinessRegisteredMail: job failed entirely', [
            'business_id' => $this->business->id,
            'error' => $e->getMessage(),
        ]);
    }
}
