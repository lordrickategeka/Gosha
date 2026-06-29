<?php

namespace App\Domains\Inventory\Models;

use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Models\WorkOrder;
use App\Domains\Organization\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'inventory_item_id',
        'branch_id',
        'movement_type',
        'quantity_change',
        'quantity_after',
        'unit_cost',
        'total_cost',
        'movable_type',
        'movable_id',
        'from_branch_id',
        'to_branch_id',
        'supplier_id',
        'performed_by',
        'notes',
        'movement_date'
    ];

    protected $casts = [
        'quantity_change' => 'decimal:2',
        'quantity_after' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'movement_date' => 'datetime'
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

    // new relationships

    public function movable(): MorphTo
    {
        return $this->morphTo();
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
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
            'transfer_in' => 'Transfer In',
            'transfer_out' => 'Transfer Out',
            'consumption' => 'Consumption',
            'work_order_use' => 'Work Order Use',
            'wash_order_use' => 'Wash Order Use',
            'wastage' => 'Wastage',
            'return_from_customer' => 'Return From Customer',
            'return_to_supplier' => 'Return To Supplier',
            'reservation' => 'Reservation',
            'reservation_release' => 'Reservation Release',
            default => ucfirst(str_replace('_', ' ', $this->movement_type)),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->movement_type) {
            'purchase' => 'success',
            'sale', 'consumption', 'work_order_use', 'wash_order_use' => 'warning',
            'adjustment', 'reservation' => 'info',
            'transfer_in', 'transfer_out', 'reservation_release' => 'secondary',
            'return_from_customer', 'return_to_supplier', 'wastage' => 'error',
            default => 'ghost',
        };
    }

    // new helpers
    public function isStockIn(): bool
    {
        return in_array($this->movement_type, [
            'purchase',
            'transfer_in',
            'return_from_customer',
            'adjustment_in'
        ], true);
    }

    public function isStockOut(): bool
    {
        return in_array($this->movement_type, [
            'work_order_use',
            'wash_order_use',
            'sale',
            'transfer_out',
            'wastage',
            'adjustment_out',
            'return_to_supplier'
        ], true);
    }

    //new scopes
    public function scopeStockIn($query)
    {
        return $query->whereIn('movement_type', [
            'purchase',
            'transfer_in',
            'return_from_customer',
            'adjustment_in'
        ]);
    }

    public function scopeStockOut($query)
    {
        return $query->whereIn('movement_type', [
            'work_order_use',
            'wash_order_use',
            'sale',
            'transfer_out',
            'wastage',
            'adjustment_out',
            'return_to_supplier'
        ]);
    }
}
