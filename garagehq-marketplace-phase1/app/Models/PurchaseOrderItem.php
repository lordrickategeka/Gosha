<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'catalog_product_id', 'description',
        'qty_ordered', 'qty_received', 'unit_price', 'tax_rate', 'line_total',
    ];

    protected $casts = [
        'qty_ordered' => 'integer',
        'qty_received' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (PurchaseOrderItem $item) {
            $base = $item->qty_ordered * (float) $item->unit_price;
            $item->line_total = round($base * (1 + ((float) $item->tax_rate / 100)), 2);
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class, 'catalog_product_id');
    }

    public function outstandingQty(): int
    {
        return max(0, $this->qty_ordered - $this->qty_received);
    }
}
