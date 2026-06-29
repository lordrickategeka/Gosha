<?php

namespace App\Domains\Marketplace\Models;

use App\Domains\Marketplace\Enums\PaymentStatus;
use App\Domains\Marketplace\Enums\PurchaseOrderStatus;
use App\Domains\Marketplace\Traits\ScopedToMarketplaceParticipant;
use App\Domains\Platform\Models\Vendor;
use App\Shared\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Cross-tenant order between a buyer vendor and a supplier vendor.
 * NOT under the global vendor scope — filter with asBuyer() / asSupplier() / forCurrentParticipant().
 */
class PurchaseOrder extends Model
{
    use ScopedToMarketplaceParticipant;
    use HasAuditLog;

    public array $participantColumns = ['buyer_vendor_id', 'supplier_vendor_id'];

    protected $fillable = [
        'po_number', 'buyer_vendor_id', 'supplier_vendor_id', 'branch_id', 'created_by',
        'source_type', 'source_id', 'subtotal', 'tax_total', 'total', 'currency',
        'status', 'payment_status', 'notes',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(MarketplaceTransaction::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'buyer_vendor_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'supplier_vendor_id');
    }

    public function isFullyReceived(): bool
    {
        $this->loadMissing('items');
        return $this->items->every(fn ($i) => $i->qty_received >= $i->qty_ordered);
    }
}
