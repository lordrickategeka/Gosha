<?php

namespace App\Domains\Vehicles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'type',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date->isPast() === false &&
               $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'inspection' => 'Inspection',
            'emissions' => 'Emissions Test',
            'insurance' => 'Insurance',
            'permit' => 'Permit',
            default => ucfirst($this->type),
        };
    }
}
