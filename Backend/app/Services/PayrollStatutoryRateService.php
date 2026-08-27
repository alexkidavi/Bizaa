<?php

namespace App\Services;

use App\Models\PayrollStatutoryRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PayrollStatutoryRateService
{
    public function list(?string $scheme = null): Collection
    {
        $query = PayrollStatutoryRate::where('business_id', Auth::user()->business_id)
            ->orderBy('scheme')
            ->orderByDesc('effective_date')
            ->orderBy('lower_limit');

        if ($scheme) {
            $query->where('scheme', $scheme);
        }

        return $query->get();
    }

    public function find(int $id): PayrollStatutoryRate
    {
        return PayrollStatutoryRate::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    protected function normalize(array $data): array
    {
        return [
            'scheme' => $data['scheme'],
            'effective_date' => $data['effective_date'],
            'lower_limit' => $data['lower_limit'] ?? 0,
            'upper_limit' => $data['upper_limit'] ?? null,
            'employee_rate' => $data['employee_rate'] ?? null,
            'employer_rate' => $data['employer_rate'] ?? null,
            'minimum_amount' => $data['minimum_amount'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];
    }

    public function create(array $data): PayrollStatutoryRate
    {
        return PayrollStatutoryRate::create(array_merge(
            ['business_id' => Auth::user()->business_id],
            $this->normalize($data)
        ));
    }

    public function update(int $id, array $data): PayrollStatutoryRate
    {
        $rate = $this->find($id);
        $rate->update($this->normalize($data));
        return $rate->fresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    /**
     * Current Kenyan statutory rates, seeded editable — NSSF (Tier I/II
     * per the 2023 Act, 2025 limits), SHIF (2.75% of gross, KES 300
     * floor), and the Affordable Housing Levy (1.5% employee + employer).
     */
    public function seedDefaults(int $businessId): void
    {
        if (PayrollStatutoryRate::where('business_id', $businessId)->exists()) {
            return;
        }

        $effectiveDate = '2025-02-01';
        $rates = [
            ['scheme' => 'nssf', 'lower_limit' => 0, 'upper_limit' => 8000, 'employee_rate' => 6, 'employer_rate' => 6],
            ['scheme' => 'nssf', 'lower_limit' => 8000.01, 'upper_limit' => 72000, 'employee_rate' => 6, 'employer_rate' => 6],
            ['scheme' => 'shif', 'lower_limit' => 0, 'upper_limit' => null, 'employee_rate' => 2.75, 'employer_rate' => null, 'minimum_amount' => 300],
            ['scheme' => 'housing_levy', 'lower_limit' => 0, 'upper_limit' => null, 'employee_rate' => 1.5, 'employer_rate' => 1.5],
        ];

        foreach ($rates as $rate) {
            PayrollStatutoryRate::create(array_merge($rate, [
                'business_id' => $businessId,
                'effective_date' => $effectiveDate,
                'is_active' => true,
            ]));
        }
    }
}
