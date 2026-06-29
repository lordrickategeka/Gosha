<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqItem extends Model
{
    protected $fillable = ['rfq_id', 'catalog_product_id', 'description', 'qty', 'target_price'];
    protected $casts = ['qty' => 'integer', 'target_price' => 'decimal:2'];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class, 'catalog_product_id');
    }

    public function label(): string
    {
        return $this->product?->name ?? (string) $this->description;
    }
}
