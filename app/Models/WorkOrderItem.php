<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'work_order_id',
        'item_type',
        'description',
        'inventory_item_id',
        'supplier_id',
        'quantity',
        'unit_price',
        'discount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = ($item->quantity * $item->unit_price) - $item->discount;
        });

        // Deduct inventory when part is used
        static::created(function ($item) {
            if ($item->item_type === 'part' && $item->inventory_item_id) {
                $item->deductInventory();
            }
        });
    }

    // Relationships
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkOrderItemImage::class);
    }

    public function partSources(): HasMany
    {
        return $this->hasMany(WorkOrderPartSource::class);
    }

    public function installationHistory(): HasMany
    {
        return $this->hasMany(PartInstallationHistory::class);
    }

    // Helpers
    public function isLabor(): bool
    {
        return $this->item_type === 'labor';
    }

    public function isPart(): bool
    {
        return $this->item_type === 'part';
    }

    protected function deductInventory(): void
    {
        if (!$this->inventoryItem) {
            return;
        }

        InventoryMovement::create([
            'inventory_item_id' => $this->inventory_item_id,
            'branch_id' => $this->workOrder->branch_id,
            'movement_type' => 'consumption',
            'quantity_change' => -$this->quantity,
            'quantity_after' => max(0, $this->inventoryItem->quantity - $this->quantity),
            'unit_cost' => $this->inventoryItem->cost_price,
            'total_cost' => $this->quantity * $this->inventoryItem->cost_price,
            'movable_type' => WorkOrder::class,
            'movable_id' => $this->work_order_id,
            'performed_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        $this->inventoryItem->decrement('quantity', $this->quantity);
    }
}
