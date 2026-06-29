<?php

namespace App\Domains\Marketplace\Models;

use App\Domains\Marketplace\Enums\QuoteStatus;
use App\Domains\Marketplace\Traits\ScopedToMarketplaceParticipant;
use App\Domains\Platform\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use ScopedToMarketplaceParticipant;

    // Quotes are written by suppliers; the buyer reaches them via the RFQ.
    public array $participantColumns = ['supplier_vendor_id'];

    protected $fillable = [
        'reference', 'rfq_id', 'supplier_vendor_id', 'subtotal', 'tax_total',
        'total', 'currency', 'valid_until', 'notes', 'status',
    ];

    protected $casts = [
        'status' => QuoteStatus::class,
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'supplier_vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function recalculateTotals(): void
    {
        $this->loadMissing('items');
        $subtotal = $this->items->sum(fn ($i) => $i->qty * (float) $i->unit_price);
        $tax = $this->items->sum(fn ($i) => $i->qty * (float) $i->unit_price * ((float) $i->tax_rate / 100));
        $this->forceFill([
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($tax, 2),
            'total' => round($subtotal + $tax, 2),
        ])->save();
    }
}
