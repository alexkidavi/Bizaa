<?php

namespace App\Services;

use App\Models\CrmPipelineStage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CrmPipelineStageService
{
    public function list(): \Illuminate\Database\Eloquent\Collection
    {
        return CrmPipelineStage::where('business_id', Auth::user()->business_id)
            ->withCount('deals')
            ->orderBy('display_order')
            ->get();
    }

    public function find(int $id): CrmPipelineStage
    {
        return CrmPipelineStage::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    protected function normalize(array $data): array
    {
        return [
            'name' => $data['name'],
            'display_order' => $data['display_order'] ?? 0,
            'is_won' => $data['is_won'] ?? false,
            'is_lost' => $data['is_lost'] ?? false,
            'default_probability' => $data['default_probability'] ?? 0,
        ];
    }

    public function create(array $data): CrmPipelineStage
    {
        return CrmPipelineStage::create(array_merge(
            ['business_id' => Auth::user()->business_id],
            $this->normalize($data)
        ));
    }

    public function update(int $id, array $data): CrmPipelineStage
    {
        $stage = $this->find($id);
        $stage->update($this->normalize($data));
        return $stage->fresh();
    }

    public function delete(int $id): void
    {
        $stage = $this->find($id);

        // pipeline_stage_id on crm_deals is required (not nullable) and
        // the FK is restrictOnDelete — this check exists so that hits a
        // clean validation message instead of a raw DB constraint error.
        if ($stage->deals()->exists()) {
            throw ValidationException::withMessages([
                'id' => ['This stage still has deals in it — move or delete them first.'],
            ]);
        }

        $stage->delete();
    }

    /**
     * A standard sales pipeline, seeded editable — same pattern as Banks,
     * Performance Ratings, and CRM Sources this session.
     */
    public function seedDefaults(int $businessId): void
    {
        $defaults = [
            ['name' => 'New', 'display_order' => 1, 'default_probability' => 10],
            ['name' => 'Contacted', 'display_order' => 2, 'default_probability' => 25],
            ['name' => 'Qualified', 'display_order' => 3, 'default_probability' => 40],
            ['name' => 'Proposal Sent', 'display_order' => 4, 'default_probability' => 60],
            ['name' => 'Negotiation', 'display_order' => 5, 'default_probability' => 80],
            ['name' => 'Won', 'display_order' => 6, 'default_probability' => 100, 'is_won' => true],
            ['name' => 'Lost', 'display_order' => 7, 'default_probability' => 0, 'is_lost' => true],
        ];

        foreach ($defaults as $stage) {
            CrmPipelineStage::firstOrCreate(
                ['business_id' => $businessId, 'name' => $stage['name']],
                array_merge($stage, ['business_id' => $businessId])
            );
        }
    }
}
