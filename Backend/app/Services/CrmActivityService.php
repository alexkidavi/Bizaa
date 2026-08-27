<?php

namespace App\Services;

use App\Models\CrmActivity;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Services\Concerns\ScopesOwnRecords;
use Illuminate\Support\Facades\Auth;

class CrmActivityService
{
    use ScopesOwnRecords;

    protected const VIEW_ALL_PERMISSION = 'crm.activities.view_all';

    protected const RELATIONS = ['contact:id,name', 'deal:id,title', 'assignee:id,name', 'creator:id,name'];

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = CrmActivity::where('business_id', Auth::user()->business_id)->with(self::RELATIONS);
        $this->applyOwnershipScope($query, self::VIEW_ALL_PERMISSION);

        if (!empty($filters['contact_id'])) {
            $query->where('contact_id', $filters['contact_id']);
        }
        if (!empty($filters['deal_id'])) {
            $query->where('deal_id', $filters['deal_id']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $status = $filters['status'] ?? '';
        $today = now()->toDateString();
        if ($status === 'upcoming') {
            $query->whereNull('completed_at')->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $today));
        } elseif ($status === 'overdue') {
            $query->whereNull('completed_at')->whereNotNull('due_date')->where('due_date', '<', $today);
        } elseif ($status === 'completed') {
            $query->whereNotNull('completed_at');
        }

        if (in_array($status, ['upcoming', 'overdue'])) {
            $query->orderByRaw('due_date IS NULL')->orderBy('due_date');
        } elseif ($status === 'completed') {
            $query->orderByDesc('completed_at');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query->get();
    }

    public function find(int $id): CrmActivity
    {
        $query = CrmActivity::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS);
        $this->applyOwnershipScope($query, self::VIEW_ALL_PERMISSION);

        return $query->firstOrFail();
    }

    public function create(array $data): CrmActivity
    {
        $businessId = Auth::user()->business_id;
        $contactId = $data['contact_id'] ?? null;
        $dealId = $data['deal_id'] ?? null;

        if ($dealId && !$contactId) {
            $deal = CrmDeal::where('id', $dealId)->where('business_id', $businessId)->firstOrFail();
            $contactId = $deal->contact_id;
        } else {
            CrmContact::where('id', $contactId)->where('business_id', $businessId)->firstOrFail();
        }

        $activity = CrmActivity::create([
            'business_id' => $businessId,
            'contact_id' => $contactId,
            'deal_id' => $dealId,
            'type' => $data['type'],
            'subject' => $data['subject'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            // Defaults to the creator so a rep without .view_all still
            // sees the activity/task they just made.
            'assigned_to' => $data['assigned_to'] ?? Auth::id(),
            'created_by' => Auth::id(),
        ]);

        return $activity->fresh(self::RELATIONS);
    }

    public function update(int $id, array $data): CrmActivity
    {
        $activity = $this->find($id);
        $activity->update([
            'type' => $data['type'],
            'subject' => $data['subject'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            // Preserve the existing assignee unless explicitly changed —
            // see CrmDealService::update() for why this matters now.
            'assigned_to' => array_key_exists('assigned_to', $data) ? $data['assigned_to'] : $activity->assigned_to,
        ]);
        return $activity->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function complete(int $id): CrmActivity
    {
        $activity = $this->find($id);
        $activity->update(['completed_at' => now()]);
        return $activity->fresh(self::RELATIONS);
    }

    public function reopen(int $id): CrmActivity
    {
        $activity = $this->find($id);
        $activity->update(['completed_at' => null]);
        return $activity->fresh(self::RELATIONS);
    }
}
