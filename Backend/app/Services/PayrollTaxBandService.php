<?php

namespace App\Services;

use App\Models\PayrollTaxBand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PayrollTaxBandService
{
    /**
     * All bands, most recent PAYE "version" (effective_date) first — the
     * frontend groups rows by effective_date to show each version as one
     * band set rather than a flat list.
     */
    public function list(): Collection
    {
        return PayrollTaxBand::where('business_id', Auth::user()->business_id)
            ->orderByDesc('effective_date')
            ->orderBy('lower_limit')
            ->get();
    }

    public function find(int $id): PayrollTaxBand
    {
        return PayrollTaxBand::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    public function create(array $data): PayrollTaxBand
    {
        return PayrollTaxBand::create([
            'business_id' => Auth::user()->business_id,
            'effective_date' => $data['effective_date'],
            'lower_limit' => $data['lower_limit'],
            'upper_limit' => $data['upper_limit'] ?? null,
            'rate' => $data['rate'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(int $id, array $data): PayrollTaxBand
    {
        $band = $this->find($id);
        $band->update([
            'effective_date' => $data['effective_date'],
            'lower_limit' => $data['lower_limit'],
            'upper_limit' => $data['upper_limit'] ?? null,
            'rate' => $data['rate'],
            'is_active' => $data['is_active'] ?? true,
        ]);
        return $band->fresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    /**
     * Current Kenyan PAYE bands (Finance Act 2023, effective 1 Jul 2023) —
     * seeded as an editable starting point, not hardcoded calculation
     * logic, so a business can adjust them if the law changes.
     */
    public function seedDefaults(int $businessId): void
    {
        if (PayrollTaxBand::where('business_id', $businessId)->exists()) {
            return;
        }

        $effectiveDate = '2023-07-01';
        $bands = [
            ['lower_limit' => 0, 'upper_limit' => 24000, 'rate' => 10],
            ['lower_limit' => 24000.01, 'upper_limit' => 32333, 'rate' => 25],
            ['lower_limit' => 32333.01, 'upper_limit' => 500000, 'rate' => 30],
            ['lower_limit' => 500000.01, 'upper_limit' => 800000, 'rate' => 32.5],
            ['lower_limit' => 800000.01, 'upper_limit' => null, 'rate' => 35],
        ];

        foreach ($bands as $band) {
            PayrollTaxBand::create(array_merge($band, [
                'business_id' => $businessId,
                'effective_date' => $effectiveDate,
                'is_active' => true,
            ]));
        }
    }
}
