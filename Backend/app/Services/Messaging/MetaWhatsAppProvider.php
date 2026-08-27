<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Sends via the Meta WhatsApp Business Cloud API. Falls back to a
 * log-only dry run automatically when phone_number_id/access_token
 * aren't configured (config/services.php `whatsapp`), so the campaign
 * pipeline can be verified before a real WhatsApp Business Account is
 * connected — same reasoning as LogSmsProvider.
 */
class MetaWhatsAppProvider implements WhatsAppProviderInterface
{
    protected function isConfigured(): bool
    {
        return (bool) config('services.whatsapp.phone_number_id') && (bool) config('services.whatsapp.access_token');
    }

    public function send(string $phone, string $message): array
    {
        if (!$this->isConfigured()) {
            info('[WhatsApp DRY RUN — no Business Account configured] would send', ['phone' => $phone, 'message' => $message]);
            return [
                'success' => true,
                'provider_message_id' => 'DRYRUN-' . Str::random(10),
                'error' => null,
            ];
        }

        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $apiVersion = config('services.whatsapp.api_version', 'v21.0');

        try {
            $response = Http::withToken(config('services.whatsapp.access_token'))
                ->timeout(15)
                ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'provider_message_id' => $response->json('messages.0.id'),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => $response->json('error.message') ?? "HTTP {$response->status()}",
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'provider_message_id' => null, 'error' => $e->getMessage()];
        }
    }
}
