<?php

namespace App\Domains\Vehicles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'make',
        'model_name',
        'engine_code',
        'fuel_type',
        'transmission_type',
        'oil_capacity_liters',
        'is_active',
    ];

    protected $casts = [
        'oil_capacity_liters' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'vehicle_model_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->make} {$this->model_name}";
    }
}
