<?php

namespace App\Services\Messaging;

use Illuminate\Support\Str;

/**
 * No SMS vendor is configured yet (see config/services.php `sms`) — this
 * logs the message instead of sending it, so the campaign pipeline
 * (recipient list building, per-recipient ledger, status transitions) can
 * be built and verified end-to-end before a real vendor is wired in.
 * Swap the SmsProviderInterface binding in AppServiceProvider to a real
 * implementation once one is chosen.
 */
class LogSmsProvider implements SmsProviderInterface
{
    public function send(string $phone, string $message): array
    {
        info('[SMS DRY RUN — no provider configured] would send', ['phone' => $phone, 'message' => $message]);

        return [
            'success' => true,
            'provider_message_id' => 'DRYRUN-' . Str::random(10),
            'error' => null,
        ];
    }
}
