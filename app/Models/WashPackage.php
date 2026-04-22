<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WashPackage extends Model
{
    use HasFactory, SoftDeletes, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'name',
        'wash_type',
        'description',
        'includes',
        'estimated_duration_minutes',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'includes' => 'array',
        'estimated_duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('wash_type', $type);
    }

    // Helpers
    public function getDurationDisplayAttribute(): string
    {
        if (!$this->estimated_duration_minutes) {
            return 'N/A';
        }

        $hours = intdiv($this->estimated_duration_minutes, 60);
        $minutes = $this->estimated_duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }
        return "{$minutes}m";
    }

    public function getIncludesListAttribute(): array
    {
        return $this->includes ?? [];
    }

    // Apply to wash order
    public function applyToWashOrder(WashOrder $washOrder, float $priceMultiplier = 1.0): void
    {
        $washOrder->items()->create([
            'description' => $this->name,
            'quantity' => 1,
            'unit_price' => $this->price * $priceMultiplier,
            'discount' => 0,
        ]);

        $washOrder->update(['wash_type' => $this->wash_type]);
    }
}
