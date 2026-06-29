<?php

namespace App\Domains\Platform\Models;

use App\Domains\Commissions\Models\CommissionRule;
use App\Domains\CRM\Models\Customer;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Inventory\Models\InventoryCategory;
use App\Domains\Inventory\Models\InventoryItem;
use App\Domains\Inventory\Models\Supplier;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Setting;
use App\Domains\ServiceConfig\Models\ServiceTemplate;
use App\Domains\ServiceConfig\Models\WashPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'logo',
        'address',
        'status',
        'trial_ends_at',
        'pricing_plan_id',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            if (empty($vendor->slug)) {
                $vendor->slug = Str::slug($vendor->name);
            }
        });
    }

    // Relationships
    public function billingConfig(): HasOne
    {
        return $this->hasOne(VendorBillingConfig::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(VendorSubscription::class)
            ->whereIn('status', [VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_TRIAL, VendorSubscription::STATUS_PAST_DUE])
            ->latest();
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function inventoryCategories(): HasMany
    {
        return $this->hasMany(InventoryCategory::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function serviceTemplates(): HasMany
    {
        return $this->hasMany(ServiceTemplate::class);
    }

    public function washPackages(): HasMany
    {
        return $this->hasMany(WashPackage::class);
    }

    public function commissionRules(): HasMany
    {
        return $this->hasMany(CommissionRule::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function platformInvoices(): HasMany
    {
        return $this->hasMany(PlatformInvoice::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isTrialExpired(): bool
    {
        return $this->isTrial() && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function mainBranch(): ?Branch
    {
        return $this->branches()->where('is_main', true)->first();
    }

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function isVatRegistered(): bool
    {
        return (bool) $this->vat_registered;
    }

    public function getDefaultVatRate(): float
    {
        return (float) ($this->default_vat_rate ?? 0);
    }
}
