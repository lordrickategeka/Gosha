<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WashOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wash_order_id',
        'description',
        'inventory_item_id',
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

        // Deduct inventory for wash supplies
        static::created(function ($item) {
            if ($item->inventory_item_id) {
                $item->deductInventory();
            }
        });
    }

    // Relationships
    public function washOrder(): BelongsTo
    {
        return $this->belongsTo(WashOrder::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    protected function deductInventory(): void
    {
        if (!$this->inventoryItem) {
            return;
        }

        InventoryMovement::create([
            'inventory_item_id' => $this->inventory_item_id,
            'branch_id' => $this->washOrder->branch_id,
            'movement_type' => 'consumption',
            'quantity' => -$this->quantity,
            'unit_cost' => $this->inventoryItem->cost_price,
            'total_cost' => $this->quantity * $this->inventoryItem->cost_price,
            'reference_type' => 'wash_order',
            'reference_id' => $this->wash_order_id,
            'performed_by' => auth()->id(),
        ]);

        $this->inventoryItem->decrement('quantity', $this->quantity);
    }
}
