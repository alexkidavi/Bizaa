<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendPersonalizedWhatsAppMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $businessId;
    protected array $data;

    public $tries   = 1;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param int $businessId
     * @param array $data Must include 'device_id', 'messages', optional 'attachment_path', and 'type'
     */
    public function __construct(int $businessId, array $data)
    {
        $this->businessId = $businessId;
        $this->data       = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $service): void
    {
        $messages = $this->data['messages'] ?? [];

        // Normalize messages array
        $normalized = [];
        foreach ($messages as $msg) {
            if (!empty($msg['number'])) {
                $normalized[] = [
                    'phone'      => $msg['number'],
                    'message'    => $msg['message'] ?? null,
                    'attachment' => $msg['attachment'] ?? $this->data['attachment_path'] ?? null,
                ];
            }
        }

        if (empty($normalized)) {
            Log::warning('No valid messages found to send.');
            return;
        }

        Log::info('📤 Dispatching personalized WhatsApp batch for ' . count($normalized) . ' numbers');

        // Send batch via WhatsAppService
        $service->processBatch(
            $this->businessId,
            $normalized,
            [
                'device_id' => $this->data['device_id'] ?? null,
                'type'      => $this->data['type'] ?? 'phonebook',
            ]
        );
    }
}
