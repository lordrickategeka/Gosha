<?php

namespace App\Domains\Marketplace\Models;

use App\Domains\Marketplace\Enums\RfqStatus;
use App\Domains\Marketplace\Traits\ScopedToMarketplaceParticipant;
use App\Domains\Platform\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rfq extends Model
{
    use ScopedToMarketplaceParticipant;

    // RFQs only ever have a buyer side. Drives scopeForCurrentParticipant().
    public array $participantColumns = ['buyer_vendor_id'];

    protected $fillable = [
        'reference', 'buyer_vendor_id', 'branch_id', 'created_by',
        'title', 'notes', 'visibility', 'status', 'closes_at',
    ];

    protected $casts = [
        'status' => RfqStatus::class,
        'closes_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(RfqInvitation::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'buyer_vendor_id');
    }

    public function isOpen(): bool
    {
        return $this->visibility === 'open';
    }
}
