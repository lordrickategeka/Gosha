<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The canonical catalog spine. Platform-owned, NOT vendor scoped.
 * Both marketplace_listings and garage inventory_items point at this.
 */
class CatalogProduct extends Model
{
    protected $fillable = [
        'brand', 'part_number', 'name', 'slug', 'category_id', 'unit_of_measure',
        'image', 'description', 'created_by_vendor_id', 'is_verified', 'is_active',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class, 'category_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class);
    }

    public function compatibleVariants(): BelongsToMany
    {
        return $this->belongsToMany(
            VehicleVariant::class,
            'part_vehicle_compatibilities',
            'catalog_product_id',
            'vehicle_variant_id'
        )->withPivot('notes')->withTimestamps();
    }

    public function scopeVerified(Builder $q): Builder
    {
        return $q->where('is_verified', true);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Products that fit any of the given vehicle variant ids. Drives recommendations. */
    public function scopeFitsVariants(Builder $q, array $variantIds): Builder
    {
        return $q->whereHas('compatibleVariants', fn (Builder $v) => $v->whereIn('vehicle_variants.id', $variantIds));
    }
}
