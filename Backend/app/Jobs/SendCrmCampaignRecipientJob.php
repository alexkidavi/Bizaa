<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\CrmCampaignRecipient;
use App\Services\Messaging\SmsProviderInterface;
use App\Services\Messaging\WhatsAppProviderInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * One recipient, one job — dispatched in a Bus::batch() by
 * CrmCampaignService::send() so a campaign to thousands of contacts no
 * longer blocks the HTTP request or is capped by how much a single
 * request can do synchronously. Idempotent: skips a recipient that's
 * already been resolved (sent/failed) by an earlier attempt, so retrying
 * a failed batch or re-queueing never double-sends.
 */
class SendCrmCampaignRecipientJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 300];

    public function __construct(protected int $recipientId)
    {
    }

    public function handle(SmsProviderInterface $smsProvider, WhatsAppProviderInterface $whatsAppProvider): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $recipient = CrmCampaignRecipient::with(['contact', 'campaign'])->find($this->recipientId);
        if (!$recipient || $recipient->status !== 'pending' || !$recipient->contact || !$recipient->campaign) {
            return;
        }

        $campaign = $recipient->campaign;
        $contact = $recipient->contact;
        $message = str_replace('{{name}}', $contact->name, $campaign->message);

        try {
            $result = match ($campaign->channel) {
                'sms' => $smsProvider->send($contact->phone, $message),
                'whatsapp' => $whatsAppProvider->send($contact->phone, $message),
                'email' => $this->sendEmail($contact->email, $campaign->subject ?? $campaign->name, $message),
            };
        } catch (\Throwable $e) {
            $result = ['success' => false, 'provider_message_id' => null, 'error' => $e->getMessage()];
        }

        $recipient->update([
            'status' => $result['success'] ? 'sent' : 'failed',
            'provider_message_id' => $result['provider_message_id'] ?? null,
            'error' => $result['error'] ?? null,
            'sent_at' => $result['success'] ? now() : null,
        ]);
    }

    protected function sendEmail(string $email, string $subject, string $body): array
    {
        try {
            Mail::to($email)->send(new CampaignMail($subject, $body));
            return ['success' => true, 'provider_message_id' => null, 'error' => null];
        } catch (\Throwable $e) {
            return ['success' => false, 'provider_message_id' => null, 'error' => $e->getMessage()];
        }
    }
}
