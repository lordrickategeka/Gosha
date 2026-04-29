<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'is_default',
        'is_featured',
        'sort_order',
        'billing_model',
        'base_price',
        'currency',
        'billing_cycle',
        'billing_cycle_days',
        'commission_rate',
        'commission_base',
        'commission_min',
        'commission_max',
        'price_per_work_order',
        'price_per_wash_order',
        'price_per_invoice',
        'price_per_user',
        'price_per_branch',
        'price_per_sms',
        'max_branches',
        'max_users',
        'max_customers',
        'max_work_orders_per_month',
        'max_wash_orders_per_month',
        'max_inventory_items',
        'max_sms_per_month',
        'storage_limit_mb',
        'features',
        'has_trial',
        'trial_days',
        'trial_requires_card',
        'setup_fee',
        'setup_fee_waivable',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_featured' => 'boolean',
        'base_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_min' => 'decimal:2',
        'commission_max' => 'decimal:2',
        'price_per_work_order' => 'decimal:2',
        'price_per_wash_order' => 'decimal:2',
        'price_per_invoice' => 'decimal:2',
        'price_per_user' => 'decimal:2',
        'price_per_branch' => 'decimal:2',
        'price_per_sms' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'features' => 'array',
        'has_trial' => 'boolean',
        'trial_requires_card' => 'boolean',
        'setup_fee_waivable' => 'boolean',
    ];

    // Billing model constants
    const MODEL_FREE = 'free';
    const MODEL_ONE_TIME = 'one_time';
    const MODEL_SUBSCRIPTION = 'subscription';
    const MODEL_COMMISSION = 'commission';
    const MODEL_HYBRID = 'hybrid';
    const MODEL_PAY_PER_USE = 'pay_per_use';
    const MODEL_CUSTOM = 'custom';

    // Billing cycles
    const CYCLE_DAILY = 'daily';
    const CYCLE_WEEKLY = 'weekly';
    const CYCLE_MONTHLY = 'monthly';
    const CYCLE_QUARTERLY = 'quarterly';
    const CYCLE_SEMI_ANNUAL = 'semi_annual';
    const CYCLE_YEARLY = 'yearly';
    const CYCLE_CUSTOM = 'custom';

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Accessors
    public function getFormattedPriceAttribute(): string
    {
        if ($this->billing_model === self::MODEL_FREE) {
            return 'Free';
        }

        $price = number_format($this->base_price);
        $cycle = $this->getBillingCycleLabel();

        return "{$this->currency} {$price}/{$cycle}";
    }

    public function getBillingCycleLabel(): string
    {
        return match($this->billing_cycle) {
            self::CYCLE_DAILY => 'day',
            self::CYCLE_WEEKLY => 'week',
            self::CYCLE_MONTHLY => 'month',
            self::CYCLE_QUARTERLY => 'quarter',
            self::CYCLE_SEMI_ANNUAL => '6 months',
            self::CYCLE_YEARLY => 'year',
            self::CYCLE_CUSTOM => "{$this->billing_cycle_days} days",
            default => 'period',
        };
    }

    public function getBillingCycleDays(): int
    {
        return match($this->billing_cycle) {
            self::CYCLE_DAILY => 1,
            self::CYCLE_WEEKLY => 7,
            self::CYCLE_MONTHLY => 30,
            self::CYCLE_QUARTERLY => 90,
            self::CYCLE_SEMI_ANNUAL => 180,
            self::CYCLE_YEARLY => 365,
            self::CYCLE_CUSTOM => $this->billing_cycle_days ?? 30,
            default => 30,
        };
    }

    public function hasSubscriptionComponent(): bool
    {
        return in_array($this->billing_model, [
            self::MODEL_SUBSCRIPTION,
            self::MODEL_HYBRID,
        ]);
    }

    public function hasCommissionComponent(): bool
    {
        return in_array($this->billing_model, [
            self::MODEL_COMMISSION,
            self::MODEL_HYBRID,
        ]);
    }

    public function hasPayPerUseComponent(): bool
    {
        return $this->billing_model === self::MODEL_PAY_PER_USE;
    }

    public function hasFeature(string $featureCode): bool
    {
        if (!$this->features) return false;
        return in_array($featureCode, $this->features);
    }

    public function isLimitReached(Vendor $vendor, string $limitType): bool
    {
        $limit = $this->{"max_{$limitType}"};
        if ($limit === null) return false; // Unlimited

        $current = match($limitType) {
            'branches' => $vendor->branches()->count(),
            'users' => $vendor->users()->count(),
            'customers' => $vendor->customers()->count(),
            'inventory_items' => $vendor->inventoryItems()->count(),
            default => 0,
        };

        return $current >= $limit;
    }

    public function getMonthlyLimitUsage(Vendor $vendor, string $metric): array
    {
        $limit = match($metric) {
            'work_orders' => $this->max_work_orders_per_month,
            'wash_orders' => $this->max_wash_orders_per_month,
            'sms' => $this->max_sms_per_month,
            default => null,
        };

        $used = VendorUsageLog::where('vendor_id', $vendor->id)
            ->where('metric', str_replace('_orders', '_order', $metric))
            ->where('billing_period', now()->format('Y-m'))
            ->sum('quantity');

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => $limit ? max(0, $limit - $used) : null,
            'percentage' => $limit ? min(100, ($used / $limit) * 100) : 0,
        ];
    }
}
