<?php

namespace App\Domains\Vehicles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'provider_name',
        'coverage_type',
        'start_date',
        'end_date',
        'max_mileage',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'max_mileage' => 'integer',
        'is_active' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    public function isExpiredByMileage(int $currentMileage): bool
    {
        return $this->max_mileage && $currentMileage > $this->max_mileage;
    }

    public function getCoverageTypeLabelAttribute(): string
    {
        return match ($this->coverage_type) {
            'bumper_to_bumper' => 'Bumper-to-Bumper',
            'powertrain' => 'Powertrain',
            'parts_specific' => 'Parts Specific',
            default => ucfirst(str_replace('_', ' ', $this->coverage_type)),
        };
    }
}
