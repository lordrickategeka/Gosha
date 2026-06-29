<?php

namespace App\Domains\ServiceConfig\Models;

use App\Domains\Operations\Models\WorkOrder;
use App\Shared\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceTemplate extends Model
{
    use HasFactory, SoftDeletes, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'category',
        'estimated_duration_minutes',
        'base_price',
        'is_active',
        'requires_road_test',
    ];

    protected $casts = [
        'estimated_duration_minutes' => 'integer',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'requires_road_test' => 'boolean',
    ];

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(ServiceTemplateItem::class);
    }

    public function laborItems(): HasMany
    {
        return $this->hasMany(ServiceTemplateItem::class)->where('item_type', 'labor');
    }

    public function partItems(): HasMany
    {
        return $this->hasMany(ServiceTemplateItem::class)->where('item_type', 'part');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Helpers
    public function getTotalPriceAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->quantity * $item->unit_price);
    }

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

    // Apply template to a work order
    public function applyToWorkOrder(WorkOrder $workOrder): void
    {
        foreach ($this->items as $templateItem) {
            $workOrder->items()->create([
                'item_type' => $templateItem->item_type,
                'description' => $templateItem->description,
                'inventory_item_id' => $templateItem->inventory_item_id,
                'quantity' => $templateItem->quantity,
                'unit_price' => $templateItem->unit_price,
                'discount' => 0,
            ]);
        }
    }
}
