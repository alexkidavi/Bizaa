<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\Customer;
use App\Services\Concerns\ScopesOwnRecords;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CrmContactService
{
    use ScopesOwnRecords;

    protected const VIEW_ALL_PERMISSION = 'crm.contacts.view_all';

    protected const RELATIONS = ['company:id,name', 'source:id,name', 'assignee:id,name', 'tags:id,name,color', 'customer:id,name,status'];

    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = CrmContact::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderByDesc('created_at');
        $this->applyOwnershipScope($query, self::VIEW_ALL_PERMISSION);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if (!empty($filters['lifecycle_stage'])) {
            $query->where('lifecycle_stage', $filters['lifecycle_stage']);
        }
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['tag_id'])) {
            $tagId = $filters['tag_id'];
            $query->whereHas('tags', fn ($q) => $q->where('crm_tags.id', $tagId));
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): CrmContact
    {
        $query = CrmContact::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS);
        $this->applyOwnershipScope($query, self::VIEW_ALL_PERMISSION);

        return $query->firstOrFail();
    }

    /**
     * Used by the contact detail page only — the deals list isn't needed
     * for list()/update()/convert(), so it's kept out of the shared
     * RELATIONS eager-load used everywhere else.
     */
    public function findWithDeals(int $id): CrmContact
    {
        $query = CrmContact::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(array_merge(self::RELATIONS, ['deals.pipelineStage:id,name']));
        $this->applyOwnershipScope($query, self::VIEW_ALL_PERMISSION);

        return $query->firstOrFail();
    }

    public function create(array $data): CrmContact
    {
        $businessId = Auth::user()->business_id;

        $contact = DB::transaction(function () use ($data, $businessId) {
            $contact = CrmContact::create([
                'business_id' => $businessId,
                'company_id' => $data['company_id'] ?? null,
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'lifecycle_stage' => $data['lifecycle_stage'] ?? 'lead',
                // Defaults to the creator so a rep without .view_all can
                // still see the contact they just made.
                'assigned_to' => $data['assigned_to'] ?? Auth::id(),
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            if (!empty($data['tag_ids'])) {
                $contact->tags()->sync($data['tag_ids']);
            }

            return $contact;
        });

        return $contact->fresh(self::RELATIONS);
    }

    public function update(int $id, array $data): CrmContact
    {
        $contact = $this->find($id);

        DB::transaction(function () use ($contact, $data) {
            $contact->update([
                'company_id' => $data['company_id'] ?? null,
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'lifecycle_stage' => $data['lifecycle_stage'] ?? $contact->lifecycle_stage,
                // Preserve the existing assignee unless explicitly changed
                // — see CrmDealService::update() for why this matters now.
                'assigned_to' => array_key_exists('assigned_to', $data) ? $data['assigned_to'] : $contact->assigned_to,
                'notes' => $data['notes'] ?? null,
            ]);

            if (array_key_exists('tag_ids', $data)) {
                $contact->tags()->sync($data['tag_ids'] ?? []);
            }
        });

        return $contact->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    /**
     * Bridges a CRM contact into the existing Sales/Quotation-facing
     * Customer table — a contact only shows up there once it's actually
     * ready to transact, not from the moment it's created as a lead.
     */
    public function convertToCustomer(int $id): CrmContact
    {
        $contact = $this->find($id);

        if ($contact->customer_id) {
            throw ValidationException::withMessages([
                'customer_id' => ['This contact is already linked to a customer.'],
            ]);
        }
        if (!$contact->phone) {
            throw ValidationException::withMessages([
                'phone' => ['A phone number is required to convert a contact to a customer.'],
            ]);
        }

        return DB::transaction(function () use ($contact) {
            $customer = Customer::create([
                'business_id' => $contact->business_id,
                'user_id' => Auth::id(),
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'status' => 'active',
                'notes' => $contact->notes,
            ]);

            $contact->update([
                'customer_id' => $customer->id,
                'lifecycle_stage' => 'customer',
            ]);

            return $contact->fresh(self::RELATIONS);
        });
    }
}
