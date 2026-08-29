<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanService
{
    /**
     * All plans for the super admin management screen — including inactive
     * ones, so they can be reactivated without going back to the seeder.
     * The public/portal-facing endpoints (SubscriptionController) already
     * filter to `active()` and are untouched by this service.
     */
    public function all()
    {
        return SubscriptionPlan::orderBy('sort_order')->orderBy('id')->get();
    }

    public function create(array $data): SubscriptionPlan
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['features'] = $this->cleanFeatures($data['features'] ?? []);
        $data['sort_order'] = $data['sort_order'] ?? ((int) SubscriptionPlan::max('sort_order') + 1);

        return SubscriptionPlan::create($data);
    }

    public function update(SubscriptionPlan $plan, array $data): SubscriptionPlan
    {
        if (array_key_exists('slug', $data) && $data['slug'] !== $plan->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $plan->id);
        }

        if (array_key_exists('features', $data)) {
            $data['features'] = $this->cleanFeatures($data['features']);
        }

        $plan->update($data);

        return $plan->fresh();
    }

    public function delete(SubscriptionPlan $plan): void
    {
        if ($plan->subscriptions()->exists()) {
            throw ValidationException::withMessages([
                'plan' => 'This plan has businesses subscribed to it (past or present) and cannot be deleted. Deactivate it instead.',
            ]);
        }

        $plan->delete();
    }

    /**
     * Persist a new relative ordering — `$orderedIds` is the full list of
     * plan ids in the order the admin wants them displayed.
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            SubscriptionPlan::whereKey($id)->update(['sort_order' => $index]);
        }
    }

    private function cleanFeatures($features): array
    {
        if (is_string($features)) {
            $features = preg_split('/\r\n|\r|\n/', $features);
        }

        return collect($features)
            ->map(fn ($f) => trim((string) $f))
            ->filter()
            ->values()
            ->all();
    }

    private function uniqueSlug(string $base, ?int  $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug;
        $i = 1;

        while (
            SubscriptionPlan::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}
