<?php
namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
class SendingWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $businessId;
    protected array $data;

    public $tries   = 1;
    public $timeout = 3600;

    public function __construct(int $businessId, array $data)
    {
        $this->businessId = $businessId;
        $this->data       = $data;
    }

    public function handle(WhatsAppService $service): void
    {
        $numbers = $this->data['numbers'] ?? [];

        // Normalize numbers array
        if (isset($numbers[0]) && is_array($numbers[0])) {
            $numbers = collect($numbers)
                ->map(fn($item) => [
                    'phone'      => $item['phone'],
                    'message'    => $item['message'] ?? $this->data['message'] ?? null,
                ])
                ->filter(fn($item) => !empty($item['phone']))
                ->values()
                ->toArray();
        } else {
            $numbers = preg_split('/[\s,]+/', trim(implode(',', (array)$numbers)));
            $numbers = array_filter(array_unique($numbers));
            $numbers = array_map(fn($num) => [
                'phone'   => $num,
                'message' => $this->data['message'] ?? null,
            ], $numbers);
        }

        info('Dispatching WhatsApp batch for ' . count($numbers) . ' numbers');

        // Pass everything to WhatsAppService
        $service->processBatch($this->businessId, $numbers, $this->data);
    }
}
