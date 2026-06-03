<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionRule extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'branch_id',
        'name',
        'role',
        'type',
        'value',
        'applies_to',
        'minimum_threshold',
        'is_active',
        'description',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

// Relationships
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeForTechnicians($query)
    {
        return $query->where('role', 'technician');
    }

    public function scopeForAttendants($query)
    {
        return $query->where('role', 'wash-attendant');
    }

    // Helpers
    public function calculateCommission(float $baseAmount): float
    {
        // Check minimum threshold
        if ($this->minimum_threshold && $baseAmount < $this->minimum_threshold) {
            return 0;
        }

        return match ($this->type) {
            'percentage' => $baseAmount * ($this->value / 100),
            'flat' => $this->value,
            default => 0,
        };
    }

    public function getDisplayValueAttribute(): string
    {
        return match ($this->type) {
            'percentage' => number_format($this->value, 1) . '%',
            'flat' => 'UGX ' . number_format($this->value),
            default => (string) $this->value,
        };
    }

    public function getAppliestoDisplayAttribute(): string
    {
        return match ($this->applies_to) {
            'labor' => 'Labor charges',
            'parts' => 'Parts sales',
            'total' => 'Total invoice',
            'wash' => 'Wash services',
            default => ucfirst($this->applies_to),
        };
    }
}
