<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    use HasFactory, BelongsToVendor;

    protected $fillable = [
       'vendor_id', 'parent_id', 'name', 'type',
        'code', 'description', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    // Inventory Types (middle level)
    public function types(): HasMany
    {
        return $this->hasMany(InventoryType::class, 'inventory_category_id');
    }

    //new relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(InventoryCategory::class, 'parent_id');
    }



    // Scopes
    public function scopeParts($query)
    {
        return $query->where('type', 'service_parts');
    }

    public function scopeWashSupplies($query)
    {
        return $query->where('type', 'wash_supplies');
    }

    public function scopeConsumables($query)
    {
        return $query->where('type', 'consumables');
    }

    // new scope
    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeServiceParts($query)
    {
        return $query->where('type', 'service_parts');
    }



    // Helpers
    public function getTotalValueAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->quantity * $item->cost_price);
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->count();
    }

    // new helper
    public function isServicePartsCategory(): bool
    {
        return $this->type === 'service_parts';
    }

    public function isWashSuppliesCategory(): bool
    {
        return $this->type === 'wash_supplies';
    }

    public function getFullPathAttribute(): string
    {
        $path = collect([$this->name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }
}
