<?php

namespace App\Services;

use App\Models\Bank;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BankService
{
    /**
     * Seeded once per business — at registration for new businesses, and
     * via a one-off backfill for businesses that existed before this
     * feature. Not a global/shared list: each business can freely rename,
     * deactivate, or add to its own copy from here on without affecting
     * any other tenant.
     */
    protected const DEFAULT_BANKS = [
        ['name' => 'Equity Bank', 'code' => '68'],
        ['name' => 'KCB Bank', 'code' => '01'],
        ['name' => 'Co-operative Bank', 'code' => '11'],
        ['name' => 'Absa Bank Kenya', 'code' => '03'],
        ['name' => 'Standard Chartered Bank', 'code' => '02'],
        ['name' => 'Diamond Trust Bank', 'code' => '63'],
        ['name' => 'NCBA Bank', 'code' => '07'],
        ['name' => 'I&M Bank', 'code' => '57'],
        ['name' => 'Stanbic Bank', 'code' => '31'],
        ['name' => 'Family Bank', 'code' => '70'],
        ['name' => 'Prime Bank', 'code' => '10'],
        ['name' => 'Sidian Bank', 'code' => '66'],
        ['name' => 'Gulf African Bank', 'code' => '72'],
        ['name' => 'National Bank of Kenya', 'code' => '12'],
        ['name' => 'Bank of Africa', 'code' => '19'],
        ['name' => 'Housing Finance Bank', 'code' => '61'],
    ];

    public function list(int $businessId, bool $activeOnly = false): Collection
    {
        $query = Bank::where('business_id', $businessId)->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function find(int $id): Bank
    {
        return Bank::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    public function create(array $data): Bank
    {
        return Bank::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(int $id, array $data): Bank
    {
        $bank = $this->find($id);
        $bank->update([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $bank;
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function seedDefaults(int $businessId): void
    {
        foreach (self::DEFAULT_BANKS as $bank) {
            Bank::firstOrCreate(
                ['business_id' => $businessId, 'name' => $bank['name']],
                ['code' => $bank['code'], 'is_active' => true]
            );
        }
    }
}
