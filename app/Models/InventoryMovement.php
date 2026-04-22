<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'inventory_item_id',
        'branch_id',
        'supplier_id',
        'movement_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'notes',
        'performed_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Polymorphic reference
    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        return match ($this->reference_type) {
            'work_order' => WorkOrder::find($this->reference_id),
            'wash_order' => WashOrder::find($this->reference_id),
            default => null,
        };
    }

    // Scopes
    public function scopePurchases($query)
    {
        return $query->where('movement_type', 'purchase');
    }

    public function scopeSales($query)
    {
        return $query->where('movement_type', 'sale');
    }

    public function scopeConsumptions($query)
    {
        return $query->where('movement_type', 'consumption');
    }

    public function scopeAdjustments($query)
    {
        return $query->where('movement_type', 'adjustment');
    }

    public function scopeForItem($query, int $itemId)
    {
        return $query->where('inventory_item_id', $itemId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // Helpers
    public function isInward(): bool
    {
        return $this->quantity > 0;
    }

    public function isOutward(): bool
    {
        return $this->quantity < 0;
    }

    public function getTypeDisplayAttribute(): string
    {
        return match ($this->movement_type) {
            'purchase' => 'Purchase',
            'sale' => 'Sale',
            'adjustment' => 'Adjustment',
            'transfer' => 'Transfer',
            'consumption' => 'Consumption',
            'return' => 'Return',
            default => ucfirst($this->movement_type),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->movement_type) {
            'purchase' => 'success',
            'sale', 'consumption' => 'warning',
            'adjustment' => 'info',
            'transfer' => 'secondary',
            'return' => 'error',
            default => 'ghost',
        };
    }
}
