<?php

namespace App\Services;

use App\Models\CrmActivity;
use App\Models\CrmCampaign;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\CrmPipelineStage;
use App\Services\Concerns\ScopesOwnRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmDashboardService
{
    use ScopesOwnRecords;

    /**
     * Aggregation-only — no new tables (Phase 5 of the CRM plan). Every
     * query is scoped to the logged-in user's business_id, same as every
     * other CRM service this module uses, and — same as CrmDealService /
     * CrmContactService / CrmActivityService — scoped down to "my own
     * records" when the user lacks the relevant .view_all permission, so
     * a rep without it can't see the whole business's pipeline value just
     * because it's rolled up into a total instead of a list.
     */
    public function overview(): array
    {
        $businessId = Auth::user()->business_id;

        $openDeals = CrmDeal::where('business_id', $businessId)->where('status', 'open');
        $this->applyOwnershipScope($openDeals, 'crm.deals.view_all');
        $wonThisMonth = CrmDeal::where('business_id', $businessId)
            ->where('status', 'won')
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]);
        $this->applyOwnershipScope($wonThisMonth, 'crm.deals.view_all');

        $contacts = CrmContact::where('business_id', $businessId);
        $this->applyOwnershipScope($contacts, 'crm.contacts.view_all');

        $overdue = CrmActivity::where('business_id', $businessId)
            ->whereNull('completed_at')
            ->whereDate('due_date', '<', now()->toDateString());
        $this->applyOwnershipScope($overdue, 'crm.activities.view_all');

        $dueToday = CrmActivity::where('business_id', $businessId)
            ->whereNull('completed_at')
            ->whereDate('due_date', '=', now()->toDateString());
        $this->applyOwnershipScope($dueToday, 'crm.activities.view_all');

        return [
            'total_contacts' => $contacts->count(),
            'open_deals_count' => (clone $openDeals)->count(),
            'open_deals_value' => (float) (clone $openDeals)->sum('value'),
            'won_this_month_count' => (clone $wonThisMonth)->count(),
            'won_this_month_value' => (float) (clone $wonThisMonth)->sum('value'),
            'overdue_activities' => $overdue->count(),
            'due_today_activities' => $dueToday->count(),
        ];
    }

    /**
     * Open-deal value and count per pipeline stage, in display order —
     * the classic "pipeline by stage" funnel chart.
     */
    public function pipelineByStage(): array
    {
        $businessId = Auth::user()->business_id;
        $canViewAllDeals = Auth::user()->hasPermission('crm.deals.view_all');
        $userId = Auth::id();

        return CrmPipelineStage::where('business_id', $businessId)
            ->orderBy('display_order')
            ->withCount(['deals as deal_count' => function ($q) use ($canViewAllDeals, $userId) {
                $q->where('status', 'open');
                if (!$canViewAllDeals) {
                    $q->where('assigned_to', $userId);
                }
            }])
            ->get()
            ->map(function (CrmPipelineStage $stage) use ($canViewAllDeals, $userId) {
                $valueQuery = $stage->deals()->where('status', 'open');
                if (!$canViewAllDeals) {
                    $valueQuery->where('assigned_to', $userId);
                }

                return [
                    'id' => $stage->id,
                    'name' => $stage->name,
                    'is_won' => $stage->is_won,
                    'is_lost' => $stage->is_lost,
                    'deal_count' => $stage->deal_count,
                    'deal_value' => (float) $valueQuery->sum('value'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Contact counts by lifecycle_stage, in the fixed lead→customer
     * progression (plus lost) rather than whatever order the DB returns —
     * this is what actually reads as a funnel.
     */
    public function conversionFunnel(): array
    {
        $businessId = Auth::user()->business_id;

        $query = CrmContact::where('business_id', $businessId);
        $this->applyOwnershipScope($query, 'crm.contacts.view_all');

        $counts = $query
            ->select('lifecycle_stage', DB::raw('count(*) as total'))
            ->groupBy('lifecycle_stage')
            ->pluck('total', 'lifecycle_stage');

        $stages = ['lead', 'qualified', 'customer', 'lost'];

        return collect($stages)->map(fn ($stage) => [
            'stage' => $stage,
            'count' => (int) ($counts[$stage] ?? 0),
        ])->all();
    }

    /**
     * Per-staff activity completion within the given range (defaults to
     * this month) — completed count, still-open count, and overdue count,
     * ranked by completions.
     */
    public function activityLeaderboard(?string $from = null, ?string $to = null): array
    {
        $businessId = Auth::user()->business_id;
        $from = $from ?: now()->startOfMonth()->toDateString();
        $to = $to ?: now()->endOfMonth()->toDateString();

        $query = CrmActivity::where('crm_activities.business_id', $businessId)
            ->whereNotNull('assigned_to');

        // Without .view_all this is a cross-staff comparison the user
        // shouldn't see either — collapse it to their own row only.
        if (!Auth::user()->hasPermission('crm.activities.view_all')) {
            $query->where('crm_activities.assigned_to', Auth::id());
        }

        $rows = $query
            ->join('users', 'users.id', '=', 'crm_activities.assigned_to')
            ->select('crm_activities.assigned_to', 'users.name as user_name')
            ->selectRaw("SUM(CASE WHEN crm_activities.completed_at IS NOT NULL AND DATE(crm_activities.completed_at) BETWEEN ? AND ? THEN 1 ELSE 0 END) as completed_count", [$from, $to])
            ->selectRaw("SUM(CASE WHEN crm_activities.completed_at IS NULL AND crm_activities.due_date >= CURDATE() THEN 1 ELSE 0 END) as open_count")
            ->selectRaw("SUM(CASE WHEN crm_activities.completed_at IS NULL AND crm_activities.due_date < CURDATE() THEN 1 ELSE 0 END) as overdue_count")
            ->groupBy('crm_activities.assigned_to', 'users.name')
            ->orderByDesc('completed_count')
            ->get();

        return $rows->map(fn ($r) => [
            'user_id' => $r->assigned_to,
            'user_name' => $r->user_name,
            'completed_count' => (int) $r->completed_count,
            'open_count' => (int) $r->open_count,
            'overdue_count' => (int) $r->overdue_count,
        ])->all();
    }

    /**
     * Recent campaigns with delivery counts — reuses the same
     * withCount shape as CrmCampaignService::list() so the numbers match
     * the Campaigns page exactly.
     */
    public function campaignPerformance(int $limit = 10): array
    {
        $businessId = Auth::user()->business_id;

        return CrmCampaign::where('business_id', $businessId)
            ->withCount([
                'recipients',
                'recipients as sent_count' => fn ($q) => $q->where('status', 'sent'),
                'recipients as failed_count' => fn ($q) => $q->where('status', 'failed'),
            ])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'channel' => $c->channel,
                'status' => $c->status,
                'recipients_count' => $c->recipients_count,
                'sent_count' => $c->sent_count,
                'failed_count' => $c->failed_count,
                'delivery_rate' => $c->recipients_count > 0 ? round(($c->sent_count / $c->recipients_count) * 100, 1) : null,
                'sent_at' => $c->sent_at,
            ])
            ->all();
    }

    public function full(?string $leaderboardFrom = null, ?string $leaderboardTo = null): array
    {
        return [
            'overview' => $this->overview(),
            'pipeline_by_stage' => $this->pipelineByStage(),
            'conversion_funnel' => $this->conversionFunnel(),
            'activity_leaderboard' => $this->activityLeaderboard($leaderboardFrom, $leaderboardTo),
            'campaign_performance' => $this->campaignPerformance(),
        ];
    }
}
