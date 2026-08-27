<?php

namespace App\Services;

use App\Jobs\SendCrmCampaignRecipientJob;
use App\Models\CrmCampaign;
use App\Models\CrmCampaignRecipient;
use App\Models\CrmContact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CrmCampaignService
{
    /**
     * Sending now runs as a Bus::batch() of SendCrmCampaignRecipientJob —
     * one job per recipient, processed by the queue worker. This is a
     * sanity cap on a single campaign, not a synchronous-request limit
     * (the old MAX_RECIPIENTS=300 constraint from before a worker was
     * running in this environment).
     */
    protected const MAX_RECIPIENTS = 5000;

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = CrmCampaign::where('business_id', Auth::user()->business_id)
            ->withCount(['recipients', 'recipients as sent_count' => fn ($q) => $q->where('status', 'sent'), 'recipients as failed_count' => fn ($q) => $q->where('status', 'failed')])
            ->orderByDesc('created_at');

        if (!empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    public function find(int $id): CrmCampaign
    {
        return CrmCampaign::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->withCount(['recipients', 'recipients as sent_count' => fn ($q) => $q->where('status', 'sent'), 'recipients as failed_count' => fn ($q) => $q->where('status', 'failed')])
            ->with(['recipients.contact:id,name,phone,email'])
            ->firstOrFail();
    }

    public function create(array $data): CrmCampaign
    {
        return CrmCampaign::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'channel' => $data['channel'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'filters' => $data['filters'] ?? [],
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);
    }

    public function update(int $id, array $data): CrmCampaign
    {
        $campaign = $this->find($id);
        if ($campaign->status !== 'draft') {
            throw ValidationException::withMessages(['status' => ['Only a draft campaign can be edited.']]);
        }
        $campaign->update([
            'name' => $data['name'],
            'channel' => $data['channel'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'filters' => $data['filters'] ?? [],
        ]);
        return $campaign->fresh();
    }

    public function delete(int $id): void
    {
        $campaign = $this->find($id);
        if ($campaign->status === 'sending') {
            throw ValidationException::withMessages(['status' => ['A campaign that is currently sending cannot be deleted.']]);
        }
        $campaign->delete();
    }

    /**
     * Resolves which contacts match a filter set, split into those with
     * the contact field the channel actually needs (phone for
     * sms/whatsapp, email for email) and those excluded for lacking it —
     * shown before sending so "this will reach N contacts, M skipped" is
     * never a surprise after the fact.
     */
    public function previewRecipients(string $channel, array $filters): array
    {
        $matching = $this->matchingContacts($filters)->get();
        $field = $channel === 'email' ? 'email' : 'phone';

        $eligible = $matching->filter(fn ($c) => !empty($c->{$field}));
        $skipped = $matching->count() - $eligible->count();

        return [
            'eligible_count' => $eligible->count(),
            'skipped_count' => $skipped,
            'sample' => $eligible->take(5)->pluck('name')->values(),
        ];
    }

    protected function matchingContacts(array $filters)
    {
        $query = CrmContact::where('business_id', Auth::user()->business_id);

        if (!empty($filters['lifecycle_stage'])) {
            $query->where('lifecycle_stage', $filters['lifecycle_stage']);
        }
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
        if (!empty($filters['source_id'])) {
            $query->where('source_id', $filters['source_id']);
        }
        if (!empty($filters['tag_id'])) {
            $tagId = $filters['tag_id'];
            $query->whereHas('tags', fn ($q) => $q->where('crm_tags.id', $tagId));
        }

        return $query;
    }

    /**
     * Builds the recipient ledger (or reuses an existing one, so
     * re-sending after a partial failure only retries what's still
     * pending), flips the campaign to 'sending', then hands every pending
     * recipient off to the queue as a Bus::batch() of one job each. The
     * batch's finally() callback — itself queued, so it also needs a
     * running worker — settles the campaign's final status once every
     * job has run. Returns immediately with status='sending'; the caller
     * doesn't wait for delivery.
     */
    public function send(int $id): CrmCampaign
    {
        $campaign = $this->find($id);

        if (!in_array($campaign->status, ['draft', 'failed'])) {
            throw ValidationException::withMessages(['status' => ['Only a draft or failed campaign can be sent.']]);
        }

        $field = $campaign->channel === 'email' ? 'email' : 'phone';
        $contacts = $this->matchingContacts($campaign->filters ?? [])
            ->whereNotNull($field)->where($field, '!=', '')
            ->limit(self::MAX_RECIPIENTS)
            ->get();

        if ($contacts->isEmpty()) {
            throw ValidationException::withMessages(['filters' => ['No contacts match this campaign\'s segment (with a usable ' . $field . ').']]);
        }

        $recipientIds = DB::transaction(function () use ($campaign, $contacts) {
            $ids = CrmCampaignRecipient::where('campaign_id', $campaign->id)
                ->where('status', 'pending')
                ->pluck('id')
                ->all();

            foreach ($contacts as $contact) {
                $recipient = CrmCampaignRecipient::firstOrCreate(
                    ['campaign_id' => $campaign->id, 'contact_id' => $contact->id],
                    ['status' => 'pending']
                );
                if ($recipient->wasRecentlyCreated) {
                    $ids[] = $recipient->id;
                }
            }

            $campaign->update(['status' => 'sending']);
            return array_values(array_unique($ids));
        });

        if (empty($recipientIds)) {
            // Nothing pending to (re)send — every recipient from a prior
            // run already resolved to sent/failed.
            $anySent = CrmCampaignRecipient::where('campaign_id', $campaign->id)->where('status', 'sent')->exists();
            $campaign->update(['status' => $anySent ? 'sent' : 'failed', 'sent_at' => now()]);
            return $this->find($campaign->id);
        }

        $jobs = collect($recipientIds)->map(fn (int $recipientId) => new SendCrmCampaignRecipientJob($recipientId))->all();

        Bus::batch($jobs)
            ->name("crm-campaign-{$campaign->id}")
            ->finally(function () use ($campaign) {
                $fresh = CrmCampaign::find($campaign->id);
                if (!$fresh) {
                    return;
                }
                $stillPending = $fresh->recipients()->where('status', 'pending')->exists();
                $anySent = $fresh->recipients()->where('status', 'sent')->exists();
                $fresh->update([
                    'status' => $stillPending ? 'sending' : ($anySent ? 'sent' : 'failed'),
                    'sent_at' => now(),
                ]);
            })
            ->dispatch();

        return $this->find($campaign->id);
    }
}
