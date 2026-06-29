<?php

namespace App\Domains\Platform\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSubscription extends Model
{
    const STATUS_TRIAL    = 'trial';
    const STATUS_ACTIVE   = 'active';
    const STATUS_PAST_DUE = 'past_due';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAUSED   = 'paused';

    protected $fillable = [
        'vendor_id',
        'pricing_plan_id',
        'status',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'custom_base_price',
        'custom_commission_rate',
        'custom_limits',
        'custom_features',
        'discount_percent',
        'discount_reason',
        'balance',
        'last_payment_at',
        'next_billing_date',
        'cancelled_at',
        'cancel_at_period_end',
        'cancellation_reason',
    ];

    protected $casts = [
        'current_period_start'  => 'datetime',
        'current_period_end'    => 'datetime',
        'trial_ends_at'         => 'datetime',
        'last_payment_at'       => 'datetime',
        'next_billing_date'     => 'date',
        'cancelled_at'          => 'datetime',
        'cancel_at_period_end'  => 'boolean',
        'custom_base_price'     => 'decimal:2',
        'custom_commission_rate' => 'decimal:2',
        'discount_percent'      => 'decimal:2',
        'balance'               => 'decimal:2',
        'custom_limits'         => 'array',
        'custom_features'       => 'array',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }

    public function invoices()
    {
        return $this->hasMany(VendorPlatformInvoice::class);
    }

    // ─── Status Helpers ────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at->isPast();
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    // ─── Pricing Helpers ───────────────────────────────────────────────────────

    public function getEffectivePrice(): float
    {
        $base = $this->custom_base_price ?? $this->plan->base_price;
        if ($this->discount_percent > 0) {
            $base = $base * (1 - ($this->discount_percent / 100));
        }
        return (float) $base;
    }

    public function getEffectiveCommissionRate(): float
    {
        return (float) ($this->custom_commission_rate ?? $this->plan->commission_rate ?? 0);
    }

    // ─── Lifecycle ─────────────────────────────────────────────────────────────

    public function cancel(string $reason = '', bool $immediately = false): void
    {
        if ($immediately) {
            $this->update([
                'status'              => self::STATUS_CANCELLED,
                'cancelled_at'        => now(),
                'cancellation_reason' => $reason,
            ]);
        } else {
            $this->update([
                'cancel_at_period_end' => true,
                'cancellation_reason'  => $reason,
            ]);
        }
    }

    public function renew(): void
    {
        $days = $this->plan->getBillingCycleDays();
        $this->update([
            'status'               => self::STATUS_ACTIVE,
            'current_period_start' => now(),
            'current_period_end'   => now()->addDays($days),
            'next_billing_date'    => now()->addDays($days),
            'last_payment_at'      => now(),
        ]);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL]);
    }
}
