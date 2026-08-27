<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\CrmPipelineStage;
use App\Models\Quotation;
use App\Models\Sale;
use App\Services\Concerns\ScopesOwnRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CrmDealService
{
    use ScopesOwnRecords;

    protected const VIEW_ALL_PERMISSION = 'crm.deals.view_all';

    protected const RELATIONS = [
        'contact:id,name,phone,email,customer_id',
        'company:id,name',
        'pipelineStage:id,name,is_won,is_lost',
        'assignee:id,name',
        'quotation:id,quotation_number,grand_total,status',
        'sale:id,reference,total',
    ];

    /**
     * Full collection, not paginated — the kanban board needs every open
     * card at once to lay out its columns. Defaults to open deals only;
     * pass status='' to see everything including won/lost.
     */
    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = CrmDeal::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderByDesc('created_at');
        $this->applyOwnershipScope($query, self::VIEW_ALL_PERMISSION);

        $status = $filters['status'] ?? 'open';
        if ($status !== '') {
            $query->where('status', $status);
        }
        if (!empty($filters['pipeline_stage_id'])) {
            $query->where('pipeline_stage_id', $filters['pipeline_stage_id']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['contact_id'])) {
            $query->where('contact_id', $filters['contact_id']);
        }

        return $query->get();
    }

    public function find(int $id): CrmDeal
    {
        $query = CrmDeal::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS);
        $this->applyOwnershipScope($query, self::VIEW_ALL_PERMISSION);

        return $query->firstOrFail();
    }

    protected function resolveStage(int $businessId, ?int $stageId): CrmPipelineStage
    {
        if ($stageId) {
            return CrmPipelineStage::where('id', $stageId)->where('business_id', $businessId)->firstOrFail();
        }
        return CrmPipelineStage::where('business_id', $businessId)->orderBy('display_order')->firstOrFail();
    }

    public function create(array $data): CrmDeal
    {
        $businessId = Auth::user()->business_id;
        $contact = CrmContact::where('id', $data['contact_id'])->where('business_id', $businessId)->firstOrFail();
        $stage = $this->resolveStage($businessId, $data['pipeline_stage_id'] ?? null);

        $deal = CrmDeal::create([
            'business_id' => $businessId,
            'contact_id' => $contact->id,
            'company_id' => $data['company_id'] ?? $contact->company_id,
            'pipeline_stage_id' => $stage->id,
            'title' => $data['title'],
            'value' => $data['value'] ?? 0,
            'probability' => $data['probability'] ?? $stage->default_probability,
            'expected_close_date' => $data['expected_close_date'] ?? null,
            // Defaults to the creator so a rep without .view_all can still
            // see the deal they just made — an explicit assignee overrides.
            'assigned_to' => $data['assigned_to'] ?? Auth::id(),
            'status' => $stage->is_won ? 'won' : ($stage->is_lost ? 'lost' : 'open'),
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return $deal->fresh(self::RELATIONS);
    }

    public function update(int $id, array $data): CrmDeal
    {
        $deal = $this->find($id);

        $deal->update([
            'company_id' => $data['company_id'] ?? $deal->company_id,
            'title' => $data['title'],
            'value' => $data['value'] ?? $deal->value,
            'probability' => $data['probability'] ?? $deal->probability,
            'expected_close_date' => $data['expected_close_date'] ?? null,
            // Preserve the existing assignee unless the caller explicitly
            // changes it — previously this always reset to null, silently
            // unassigning (and now hiding, per ownership scoping) the deal
            // on any save that didn't carry assigned_to.
            'assigned_to' => array_key_exists('assigned_to', $data) ? $data['assigned_to'] : $deal->assigned_to,
            'notes' => $data['notes'] ?? null,
        ]);

        return $deal->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    /**
     * The kanban drag action — moving a card to a column changes both its
     * stage and, derived from that stage's is_won/is_lost flags, its
     * status. Moving a won/lost deal back to a middle column reopens it.
     */
    public function moveStage(int $id, int $stageId): CrmDeal
    {
        $deal = $this->find($id);
        $stage = CrmPipelineStage::where('id', $stageId)->where('business_id', $deal->business_id)->firstOrFail();

        $deal->update([
            'pipeline_stage_id' => $stage->id,
            'probability' => $stage->default_probability,
            'status' => $stage->is_won ? 'won' : ($stage->is_lost ? 'lost' : 'open'),
            'lost_reason' => $stage->is_lost ? $deal->lost_reason : null,
        ]);

        return $deal->fresh(self::RELATIONS);
    }

    public function markWon(int $id): CrmDeal
    {
        $deal = $this->find($id);
        $wonStage = CrmPipelineStage::where('business_id', $deal->business_id)->where('is_won', true)->first();

        if (!$wonStage) {
            throw ValidationException::withMessages(['pipeline_stage_id' => ['No pipeline stage is marked as "Won" — configure one first.']]);
        }

        return $this->moveStage($id, $wonStage->id);
    }

    public function markLost(int $id, string $reason): CrmDeal
    {
        $deal = $this->find($id);
        $lostStage = CrmPipelineStage::where('business_id', $deal->business_id)->where('is_lost', true)->first();

        if (!$lostStage) {
            throw ValidationException::withMessages(['pipeline_stage_id' => ['No pipeline stage is marked as "Lost" — configure one first.']]);
        }

        $deal = $this->moveStage($id, $lostStage->id);
        $deal->update(['lost_reason' => $reason]);

        return $deal->fresh(self::RELATIONS);
    }

    public function linkQuotation(int $id, int $quotationId): CrmDeal
    {
        $deal = $this->find($id);
        $quotation = Quotation::where('id', $quotationId)->where('business_id', $deal->business_id)->firstOrFail();

        $this->guardCustomerMatch($deal, $quotation->customer_id, 'quotation');

        $deal->update(['quotation_id' => $quotation->id]);
        return $deal->fresh(self::RELATIONS);
    }

    public function linkSale(int $id, int $saleId): CrmDeal
    {
        $deal = $this->find($id);
        $sale = Sale::where('id', $saleId)->where('business_id', $deal->business_id)->firstOrFail();

        $this->guardCustomerMatch($deal, $sale->customer_id, 'sale');

        $deal->update(['sale_id' => $sale->id]);
        return $deal->fresh(self::RELATIONS);
    }

    protected function guardCustomerMatch(CrmDeal $deal, ?int $documentCustomerId, string $label): void
    {
        $contactCustomerId = $deal->contact?->customer_id;

        if (!$contactCustomerId) {
            throw ValidationException::withMessages([
                'contact_id' => ["This deal's contact hasn't been converted to a customer yet — convert it first so its {$label}s can be linked."],
            ]);
        }

        if ($documentCustomerId !== $contactCustomerId) {
            throw ValidationException::withMessages([
                $label . '_id' => ["That {$label} belongs to a different customer than this deal's contact."],
            ]);
        }
    }
}
