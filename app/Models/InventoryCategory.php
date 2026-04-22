<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryCategory extends Model
{
    use HasFactory, SoftDeletes, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'name',
        'type',
        'description',
    ];

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'category_id');
    }

    // Scopes
    public function scopeParts($query)
    {
        return $query->where('type', 'parts');
    }

    public function scopeWashSupplies($query)
    {
        return $query->where('type', 'wash_supplies');
    }

    public function scopeConsumables($query)
    {
        return $query->where('type', 'consumables');
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
}
