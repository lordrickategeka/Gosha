<?php

namespace App\Models;

use App\Traits\ScopedToMarketplaceParticipant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceTransaction extends Model
{
    use ScopedToMarketplaceParticipant;

    public array $participantColumns = ['buyer_vendor_id', 'supplier_vendor_id'];

    protected $fillable = [
        'purchase_order_id', 'buyer_vendor_id', 'supplier_vendor_id', 'gross_amount',
        'commission_rate', 'commission_amount', 'currency', 'status',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
