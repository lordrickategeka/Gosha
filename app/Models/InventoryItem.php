<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory, BelongsToVendor, HasAuditLog;

    protected $fillable = [
        'vendor_id',
        'branch_id',
        'category_id',
        'supplier_id',
        'item_type',
        'name',
        'brand',
        'sku',
        'barcode',
        'oem_number',
        'aftermarket_number',
        'position',
        'condition',
        'unit',
        'unit_of_measure',
        'quantity',
        'reorder_level',
        'cost_price',
        'selling_price',
        'usage_rate',
        'expiry_date',
        'location',
        'description',
        'notes',
        'image_path',
        'is_active'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'usage_rate' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }


    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function workOrderItems(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function washOrderItems(): HasMany
    {
        return $this->hasMany(WashOrderItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForOtherBranches($query, int $currentBranchId)
    {
        return $query->whereNotNull('branch_id')->where('branch_id', '!=', $currentBranchId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeParts($query)
    {
        return $query->whereHas('category', fn($q) => $q->where('type', 'parts'));
    }


    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhereHas('part', fn($p) => $p->where('name', 'like', "%{$search}%")
                    ->orWhere('oem_number', 'like', "%{$search}%")
                    ->orWhere('aftermarket_number', 'like', "%{$search}%"));
        });
    }

    public function scopeCompatibleWith($query, int $vehicleVariantId)
    {
        return $query->whereHas('part.compatibleVehicles', function ($q) use ($vehicleVariantId) {
            $q->where('vehicle_variant_id', $vehicleVariantId);
        });
    }

    public function scopeByCondition($query, string $condition)
    {
        return $query->where('condition', $condition);
    }

    public function scopeWithPart($query)
    {
        return $query->whereNotNull('part_id');
    }

    public function scopeWithoutPart($query)
    {
        return $query->whereNull('part_id');
    }

    //new scopes for inventory types
    public function scopeServiceParts($query)
    {
        return $query->where('item_type', 'service_part');
    }

    public function scopeWashSupplies($query)
    {
        return $query->where('item_type', 'wash_supply');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<', now());
    }
    // ------------

    // Helpers
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }
        if ($this->isLowStock()) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getStockStatusColorAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'error',
            'low_stock' => 'warning',
            'in_stock' => 'success',
            default => 'ghost',
        };
    }

    public function getTotalValueAttribute(): float
    {
        return $this->quantity * $this->cost_price;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price <= 0) {
            return 0;
        }
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    // Stock operations
    public function addStock(float $quantity, ?int $supplierId = null, ?float $unitCost = null, ?string $notes = null): InventoryMovement
    {
        $movement = InventoryMovement::create([
            'inventory_item_id' => $this->id,
            'supplier_id' => $supplierId,
            'movement_type' => 'purchase',
            'quantity' => $quantity,
            'unit_cost' => $unitCost ?? $this->cost_price,
            'total_cost' => $quantity * ($unitCost ?? $this->cost_price),
            'notes' => $notes,
            'performed_by' => auth()->id(),
        ]);

        $this->increment('quantity', $quantity);

        // Update cost price if different
        if ($unitCost && $unitCost !== $this->cost_price) {
            $this->update(['cost_price' => $unitCost]);
        }

        return $movement;
    }

    public function adjustStock(float $quantity, ?string $notes = null): InventoryMovement
    {
        $movement = InventoryMovement::create([
            'inventory_item_id' => $this->id,
            'movement_type' => 'adjustment',
            'quantity' => $quantity,
            'notes' => $notes,
            'performed_by' => auth()->id(),
        ]);

        $this->increment('quantity', $quantity);

        return $movement;
    }

    public function transferStock(float $quantity, int $toBranchId, ?string $notes = null): InventoryMovement
    {
        $movement = InventoryMovement::create([
            'inventory_item_id' => $this->id,
            'branch_id' => $toBranchId,
            'movement_type' => 'transfer',
            'quantity' => -$quantity,
            'notes' => $notes,
            'performed_by' => auth()->id(),
        ]);

        return $movement;
    }


    // Type checks -new helpers for inventory types

    public function isServicePart(): bool
    {
        return $this->item_type === 'service_part';
    }

    public function isWashSupply(): bool
    {
        return $this->item_type === 'wash_supply';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date
            && $this->expiry_date->lte(now()->addDays($days))
            && !$this->isExpired();
    }

    // Stock operations
    public function consumeForWorkOrder(WorkOrder $workOrder, float $quantity, ?string $notes = null): InventoryMovement
    {
        $movement = InventoryMovement::create([
            'inventory_item_id' => $this->id,
            'branch_id' => $workOrder->branch_id,
            'movement_type' => 'work_order_use',
            'quantity_change' => -$quantity,
            'quantity_after' => $this->quantity - $quantity,
            'unit_cost' => $this->cost_price,
            'total_cost' => $quantity * $this->cost_price,
            'movable_type' => WorkOrder::class,
            'movable_id' => $workOrder->id,
            'performed_by' => auth()->id(),
            'notes' => $notes,
            'movement_date' => now(),
        ]);

        $this->decrement('quantity', $quantity);

        return $movement;
    }

    public function consumeForWashOrder(WashOrder $washOrder, float $quantity, ?string $notes = null): InventoryMovement
    {
        $movement = InventoryMovement::create([
            'inventory_item_id' => $this->id,
            'branch_id' => $washOrder->branch_id,
            'movement_type' => 'wash_order_use',
            'quantity_change' => -$quantity,
            'quantity_after' => $this->quantity - $quantity,
            'unit_cost' => $this->cost_price,
            'total_cost' => $quantity * $this->cost_price,
            'movable_type' => WashOrder::class,
            'movable_id' => $washOrder->id,
            'performed_by' => auth()->id(),
            'notes' => $notes,
            'movement_date' => now(),
        ]);

        $this->decrement('quantity', $quantity);

        return $movement;
    }
}
