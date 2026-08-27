<?php

namespace App\Services\Messaging;

interface SmsProviderInterface
{
    /**
     * @return array{success: bool, provider_message_id: ?string, error: ?string}
     */
    public function send(string $phone, string $message): array;
}
