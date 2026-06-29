<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A supplier's offer of a catalog product. Cross-tenant — NOT under the global vendor scope.
 * Two deliberate views:
 *   browsable()        -> buyer view, every active listing regardless of ownership
 *   ownedBySupplier()  -> supplier management view, current vendor's own listings
 */
class MarketplaceListing extends Model
{
    protected $fillable = [
        'supplier_vendor_id', 'catalog_product_id', 'supplier_sku', 'price', 'currency',
        'stock_qty', 'min_order_qty', 'lead_time_days', 'condition', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock_qty' => 'integer',
        'min_order_qty' => 'integer',
        'lead_time_days' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Vendor::class, 'supplier_vendor_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class, 'catalog_product_id');
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ListingPriceTier::class)->orderBy('min_qty');
    }

    /** Buyer-facing: all active listings, ownership ignored. */
    public function scopeBrowsable(Builder $q): Builder
    {
        return $q->where('is_active', true)->where('stock_qty', '>', 0);
    }

    /** Supplier-facing: only the current (or given) vendor's listings. */
    public function scopeOwnedBySupplier(Builder $q, ?int $vendorId = null): Builder
    {
        $vendorId ??= session('current_vendor_id') ?? optional(auth()->user())->vendor_id;

        return $q->where('supplier_vendor_id', $vendorId);
    }

    /** Resolve the unit price for a quantity, honouring the highest applicable tier. */
    public function priceForQty(int $qty): float
    {
        $tier = $this->priceTiers
            ->filter(fn ($t) => $qty >= $t->min_qty)
            ->sortByDesc('min_qty')
            ->first();

        return (float) ($tier->unit_price ?? $this->price);
    }
}
