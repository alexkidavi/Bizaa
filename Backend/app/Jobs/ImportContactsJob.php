<?php
namespace App\Jobs;

use App\Http\Helpers\PhoneHelper;
use App\Models\PhonebookContact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportContactsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $phonebookId;

    public function __construct($filePath, $phonebookId)
    {
        $this->filePath    = $filePath;
        $this->phonebookId = $phonebookId;
    }

    public function handle()
    {
        $path = storage_path('app/private/' . $this->filePath);

        if (! file_exists($path)) {
            \Log::error("❌ Excel file not found: {$path}");
            return;
        }

        $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $path)[0] ?? [];

        if (empty($rows)) {
            \Log::warning("⚠️ No rows found in Excel file: {$path}");
            return;
        }

        $header    = array_map('strtolower', $rows[0]);
        $contacts  = [];
        $timestamp = now();

        $existingPhones = PhonebookContact::where('phonebook_id', $this->phonebookId)
            ->pluck('phone')
            ->map(fn($p) => PhoneHelper::normalize($p))
            ->toArray();

        foreach (array_slice($rows, 1) as $row) {
            $data = array_combine($header, $row);

            if (! empty($data['phone'])) {
                $cleanPhone = PhoneHelper::normalize($data['phone']);
                if (! $cleanPhone || in_array($cleanPhone, $existingPhones)) {
                    continue;
                }

                $contacts[] = [
                    'phonebook_id' => $this->phonebookId,
                    'name'         => $data['name'] ?? 'Unknown',
                    'phone'        => $cleanPhone,
                    'email'        => $data['email'] ?? null,
                    'created_at'   => $timestamp,
                    'updated_at'   => $timestamp,
                ];

                $existingPhones[] = $cleanPhone;
            }
        }

        if (! empty($contacts)) {
            collect($contacts)->chunk(500)->each(function ($chunk) {
                PhonebookContact::insert($chunk->toArray());
            });
        }

        \Log::info("✅ Imported " . count($contacts) . " unique contacts for phonebook {$this->phonebookId}");
    }

}
