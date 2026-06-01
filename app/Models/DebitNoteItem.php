<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitNoteItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'debit_note_id',
        'work_order_item_id',
        'inventory_item_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'total',
        'customer_decision',
        'rejection_reason',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $item) {
            $item->total = ($item->quantity * $item->unit_price) - ($item->discount ?? 0);
        });
    }

    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(DebitNote::class);
    }

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(WorkOrderItem::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function isPending(): bool
    {
        return $this->customer_decision === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->customer_decision === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->customer_decision === 'rejected';
    }
}
