<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'inventory_item_id',
        'supplier_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'vat_applicable',
        'vat_rate',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'quantity'       => 'decimal:2',
        'unit_price'     => 'decimal:2',
        'discount'       => 'decimal:2',
        'vat_rate'       => 'decimal:2',
        'total'          => 'decimal:2',
        'vat_applicable' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = max(0, ($item->quantity * $item->unit_price) - $item->discount);
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    public function vatAmount(): float
    {
        if (!$this->vat_applicable || $this->vat_rate <= 0) {
            return 0;
        }

        return round($this->total * ($this->vat_rate / 100), 2);
    }

    public function totalWithVat(): float
    {
        return round($this->total + $this->vatAmount(), 2);
    }
}
