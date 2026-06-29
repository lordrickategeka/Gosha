<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformInvoiceItem extends Model
{
    protected $fillable = [
        'platform_invoice_id',
        'description',
        'type',
        'quantity',
        'unit_price',
        'amount',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(VendorPlatformInvoice::class, 'platform_invoice_id');
    }
}
