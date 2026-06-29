<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The stock-in trigger. GoodsReceiptItemObserver listens on 'created' and:
 *   1. increments the matching garage inventory_item stock (creating it if absent), and
 *   2. bumps purchase_order_items.qty_received,
 * mirroring the existing observer pattern used for inventory consumption.
 */
class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id', 'purchase_order_item_id', 'qty_received', 'inventory_item_id',
    ];

    protected $casts = ['qty_received' => 'integer'];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
