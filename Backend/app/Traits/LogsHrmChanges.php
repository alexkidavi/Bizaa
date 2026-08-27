<?php

namespace App\Traits;

use App\Models\HrmAuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Writes a row to hrm_audit_logs whenever a model using this trait is
 * created, updated, or deleted — so HR records keep a visible history
 * instead of changes silently overwriting each other. One shared table
 * across every HRM entity rather than a bespoke history table per model.
 */
trait LogsHrmChanges
{
    public static function bootLogsHrmChanges(): void
    {
        static::created(function ($model) {
            $model->writeAuditLog('created', $model->only($model->getFillable()));
        });

        static::updated(function ($model) {
            $changes = collect($model->getChanges())
                ->except(['updated_at'])
                ->mapWithKeys(fn ($new, $field) => [
                    $field => [$model->getOriginal($field), $new],
                ])
                ->all();

            if (!empty($changes)) {
                $model->writeAuditLog('updated', $changes);
            }
        });

        static::deleted(function ($model) {
            $model->writeAuditLog('deleted', []);
        });
    }

    public function auditLogs()
    {
        return $this->morphMany(HrmAuditLog::class, 'auditable')->latest('created_at');
    }

    /**
     * For transitions that carry a reason (status changes, manual
     * balance adjustments) — call explicitly from the service layer,
     * since those aren't plain attribute changes on this model.
     */
    public function logHrmAction(string $action, array $changes = [], ?string $reason = null): void
    {
        $this->writeAuditLog($action, $changes, $reason);
    }

    protected function writeAuditLog(string $action, array $changes, ?string $reason = null): void
    {
        HrmAuditLog::create([
            'business_id' => $this->business_id,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $changes,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }
}
