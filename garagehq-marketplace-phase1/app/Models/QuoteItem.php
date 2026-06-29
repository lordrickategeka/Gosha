<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id', 'rfq_item_id', 'catalog_product_id', 'description',
        'qty', 'unit_price', 'tax_rate', 'line_total',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Keep line_total consistent without relying on the caller.
        static::saving(function (QuoteItem $item) {
            $base = $item->qty * (float) $item->unit_price;
            $item->line_total = round($base * (1 + ((float) $item->tax_rate / 100)), 2);
        });
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function rfqItem(): BelongsTo
    {
        return $this->belongsTo(RfqItem::class);
    }
}
