<?php

namespace App\Traits;

use App\Models\AuditLog;

trait HasAuditLog
{
    protected static function bootHasAuditLog(): void
    {
        static::created(function ($model) {
            $model->logAudit('created', null, $model->toArray());
        });

        static::updated(function ($model) {
            $model->logAudit('updated', $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->toArray(), null);
        });
    }

    public function logAudit(string $action, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): void
    {
        // Don't log if no user or during seeding
        if (!auth()->check() && !app()->runningInConsole()) {
            return;
        }

        AuditLog::create([
            'vendor_id' => $this->vendor_id ?? (auth()->user()?->vendor_id),
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'description' => $description,
        ]);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
