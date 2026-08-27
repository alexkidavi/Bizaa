<?php

namespace App\Services;

use App\Models\CrmCompany;
use Illuminate\Support\Facades\Auth;

class CrmCompanyService
{
    public function list(?string $search = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = CrmCompany::where('business_id', Auth::user()->business_id)
            ->withCount('contacts')
            ->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->get();
    }

    public function find(int $id): CrmCompany
    {
        return CrmCompany::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->withCount('contacts')
            ->firstOrFail();
    }

    public function create(array $data): CrmCompany
    {
        return CrmCompany::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'industry' => $data['industry'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'website' => $data['website'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);
    }

    public function update(int $id, array $data): CrmCompany
    {
        $company = $this->find($id);
        $company->update([
            'name' => $data['name'],
            'industry' => $data['industry'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'website' => $data['website'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
        return $company->fresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }
}
