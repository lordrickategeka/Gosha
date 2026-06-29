<?php

namespace App\Domains\ServiceConfig\Models;

use App\Domains\Inventory\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_template_id',
        'item_type',
        'description',
        'inventory_item_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    // Relationships
    public function serviceTemplate(): BelongsTo
    {
        return $this->belongsTo(ServiceTemplate::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    // Helpers
    public function getTotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function isLabor(): bool
    {
        return $this->item_type === 'labor';
    }

    public function isPart(): bool
    {
        return $this->item_type === 'part';
    }
}
