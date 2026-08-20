<?php

namespace App\Domains\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformPayment extends Model
{
    protected $fillable = [
        'vendor_id',
        'platform_invoice_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'reference_number',
        'transaction_id',
        'mobile_money_number',
        'bank_reference',
        'paid_at',
        'received_by',
        'notes',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'gateway_response' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(VendorPlatformInvoice::class, 'platform_invoice_id');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
