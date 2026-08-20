<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorPlatformInvoice extends Model
{
    protected $fillable = [
        'vendor_id',
        'vendor_subscription_id',
        'invoice_number',
        'type',
        'status',
        'period_start',
        'period_end',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'amount_paid',
        'balance_due',
        'currency',
        'issue_date',
        'due_date',
        'paid_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    const TYPE_SUBSCRIPTION = 'subscription';
    const TYPE_COMMISSION = 'commission';
    const TYPE_USAGE = 'usage';
    const TYPE_SETUP_FEE = 'setup_fee';
    const TYPE_OVERAGE = 'overage';
    const TYPE_ADDON = 'addon';
    const TYPE_CREDIT = 'credit';
    const TYPE_REFUND = 'refund';

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIALLY_PAID = 'partially_paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
            if (!$invoice->issue_date) {
                $invoice->issue_date = now();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'PLT';
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        return "{$prefix}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(VendorSubscription::class, 'vendor_subscription_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PlatformInvoiceItem::class, 'platform_invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PlatformPayment::class, 'platform_invoice_id');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(VendorUsageLog::class, 'platform_invoice_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
            ->orWhere(function ($q) {
                $q->where('status', self::STATUS_PENDING)
                  ->where('due_date', '<', now());
            });
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIALLY_PAID, self::STATUS_OVERDUE]);
    }

    // Helpers
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE ||
               ($this->status === self::STATUS_PENDING && $this->due_date < now());
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('amount');
        $this->total = $this->subtotal - $this->discount_amount + $this->tax_amount;
        $this->balance_due = $this->total - $this->amount_paid;

        if ($this->balance_due <= 0 && $this->total > 0) {
            $this->status = self::STATUS_PAID;
            $this->paid_at = $this->paid_at ?? now();
        } elseif ($this->amount_paid > 0) {
            $this->status = self::STATUS_PARTIALLY_PAID;
        }

        $this->save();
    }

    public function addItem(string $description, float $amount, string $type = null, array $metadata = []): PlatformInvoiceItem
    {
        return $this->items()->create([
            'description' => $description,
            'type' => $type,
            'quantity' => 1,
            'unit_price' => $amount,
            'amount' => $amount,
            'metadata' => $metadata,
        ]);
    }

    public function recordPayment(float $amount, string $method, array $details = []): PlatformPayment
    {
        $payment = PlatformPayment::create([
            'vendor_id' => $this->vendor_id,
            'platform_invoice_id' => $this->id,
            'amount' => $amount,
            'currency' => $this->currency,
            'payment_method' => $method,
            'status' => 'completed',
            'reference_number' => $details['reference_number'] ?? null,
            'transaction_id' => $details['transaction_id'] ?? null,
            'paid_at' => now(),
            'received_by' => auth()->id(),
            'notes' => $details['notes'] ?? null,
        ]);

        $this->amount_paid += $amount;
        $this->calculateTotals();

        return $payment;
    }

    public function markOverdue(): void
    {
        if ($this->status === self::STATUS_PENDING && $this->due_date < now()) {
            $this->status = self::STATUS_OVERDUE;
            $this->save();
        }
    }
}
