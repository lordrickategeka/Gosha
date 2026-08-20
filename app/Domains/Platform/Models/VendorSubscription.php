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
        'auto_renew',
        'grace_ends_at',
        'locked_at',
        'flutterwave_card_token',
        'flutterwave_customer_email',
    ];

    protected $casts = [
        'current_period_start'  => 'datetime',
        'current_period_end'    => 'datetime',
        'trial_ends_at'         => 'datetime',
        'last_payment_at'       => 'datetime',
        'next_billing_date'     => 'date',
        'cancelled_at'          => 'datetime',
        'cancel_at_period_end'  => 'datetime',
        'custom_base_price'     => 'decimal:2',
        'custom_commission_rate' => 'decimal:2',
        'discount_percent'      => 'decimal:2',
        'balance'               => 'decimal:2',
        'custom_limits'         => 'array',
        'custom_features'       => 'array',
        'auto_renew'            => 'boolean',
        'grace_ends_at'         => 'datetime',
        'locked_at'             => 'datetime',
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

    public function isInGracePeriod(): bool
    {
        return $this->isPastDue()
            && $this->grace_ends_at
            && $this->grace_ends_at->isFuture()
            && !$this->locked_at;
    }

    public function isLocked(): bool
    {
        return $this->isPastDue()
            && $this->grace_ends_at
            && $this->grace_ends_at->isPast();
    }

    public function graceDaysRemaining(): int
    {
        if (!$this->isInGracePeriod()) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInHours($this->grace_ends_at, false) / 24));
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
                'cancel_at_period_end' => $this->current_period_end,
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

    /**
     * Trial has ended: start real billing on the plan (invoice generation is
     * handled separately by BillingService so it can decide amounts/dates).
     */
    public function activate(): void
    {
        $days = $this->plan->getBillingCycleDays();
        $this->update([
            'status'               => self::STATUS_ACTIVE,
            'current_period_start' => now(),
            'current_period_end'   => now()->addDays($days),
            'next_billing_date'    => now(),
        ]);
    }

    public function markPastDue(Carbon $graceEndsAt): void
    {
        $this->update([
            'status'        => self::STATUS_PAST_DUE,
            'grace_ends_at' => $graceEndsAt,
        ]);
    }

    public function markLocked(): void
    {
        $this->update(['locked_at' => now()]);
    }

    /**
     * Payment came in: clear past-due/grace/lock state and go back to active.
     */
    public function reactivate(): void
    {
        $this->update([
            'status'         => self::STATUS_ACTIVE,
            'grace_ends_at'  => null,
            'locked_at'      => null,
            'last_payment_at' => now(),
        ]);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL]);
    }
}
