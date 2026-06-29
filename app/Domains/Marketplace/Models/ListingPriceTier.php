<?php

namespace App\Domains\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingPriceTier extends Model
{
    protected $fillable = ['marketplace_listing_id', 'min_qty', 'unit_price'];
    protected $casts = ['unit_price' => 'decimal:2', 'min_qty' => 'integer'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(MarketplaceListing::class, 'marketplace_listing_id');
    }
}
