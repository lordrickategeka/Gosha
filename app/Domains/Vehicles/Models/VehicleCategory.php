<?php

namespace App\Domains\Vehicles\Models;

use App\Shared\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleCategory extends Model
{
    use HasFactory, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'name',
        'price_multiplier',
        'sort_order',
    ];

    protected $casts = [
        'price_multiplier' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helpers
    public function getMultiplierDisplayAttribute(): string
    {
        if ($this->price_multiplier == 1.0) {
            return 'Standard';
        }

        $percent = ($this->price_multiplier - 1) * 100;
        return sprintf('%+.0f%%', $percent);
    }

    public function calculatePrice(float $basePrice): float
    {
        return $basePrice * $this->price_multiplier;
    }
}
