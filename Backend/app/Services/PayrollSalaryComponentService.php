<?php

namespace App\Services;

use App\Models\PayrollSalaryComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PayrollSalaryComponentService
{
    public function list(bool $activeOnly = false): Collection
    {
        $query = PayrollSalaryComponent::where('business_id', Auth::user()->business_id)
            ->orderBy('type')
            ->orderBy('display_order')
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function find(int $id): PayrollSalaryComponent
    {
        return PayrollSalaryComponent::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    protected function normalize(array $data): array
    {
        $calculationType = $data['calculation_type'] ?? 'fixed';

        return [
            'name' => $data['name'],
            'code' => strtoupper(str_replace(' ', '_', trim($data['code']))),
            'type' => $data['type'],
            'calculation_type' => $calculationType,
            'amount' => $calculationType === 'fixed' ? ($data['amount'] ?? 0) : null,
            'percentage' => $calculationType === 'percentage_of_basic' ? ($data['percentage'] ?? 0) : null,
            'is_taxable' => $data['is_taxable'] ?? true,
            'is_active' => $data['is_active'] ?? true,
            'display_order' => $data['display_order'] ?? 0,
            'description' => $data['description'] ?? null,
        ];
    }

    public function create(array $data): PayrollSalaryComponent
    {
        return PayrollSalaryComponent::create(array_merge(
            ['business_id' => Auth::user()->business_id],
            $this->normalize($data)
        ));
    }

    public function update(int $id, array $data): PayrollSalaryComponent
    {
        $component = $this->find($id);
        $component->update($this->normalize($data));
        return $component->fresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }
}
