<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Polymorphic relation to auditable model
    public function auditable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForModel($query, string $modelClass)
    {
        return $query->where('auditable_type', $modelClass);
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Helpers
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'error',
            'status_changed' => 'warning',
            'login' => 'primary',
            'logout' => 'secondary',
            default => 'ghost',
        };
    }

    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'created' => 'plus-circle',
            'updated' => 'pencil',
            'deleted' => 'trash',
            'status_changed' => 'refresh',
            'login' => 'login',
            'logout' => 'logout',
            default => 'info',
        };
    }

    public function getModelNameAttribute(): string
    {
        if (!$this->auditable_type) {
            return 'Unknown';
        }

        $parts = explode('\\', $this->auditable_type);
        return end($parts);
    }

    public function getChangedFieldsAttribute(): array
    {
        if (!$this->new_values) {
            return [];
        }

        return array_keys($this->new_values);
    }

    public function hasFieldChanged(string $field): bool
    {
        return isset($this->new_values[$field]);
    }

    public function getFieldChange(string $field): ?array
    {
        if (!$this->hasFieldChanged($field)) {
            return null;
        }

        return [
            'old' => $this->old_values[$field] ?? null,
            'new' => $this->new_values[$field] ?? null,
        ];
    }

    // Static logging methods
    public static function logLogin(User $user): self
    {
        return static::create([
            'vendor_id' => $user->vendor_id,
            'user_id' => $user->id,
            'action' => 'login',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'description' => "User {$user->name} logged in",
        ]);
    }

    public static function logLogout(User $user): self
    {
        return static::create([
            'vendor_id' => $user->vendor_id,
            'user_id' => $user->id,
            'action' => 'logout',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'description' => "User {$user->name} logged out",
        ]);
    }
}
