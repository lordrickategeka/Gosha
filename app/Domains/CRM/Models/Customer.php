<?php

namespace App\Domains\CRM\Models;

use App\Shared\Traits\BelongsToVendor;
use App\Shared\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Vehicles\Models\Vehicle;
use App\Domains\Operations\Models\Appointment;
use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Models\WorkOrder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes, BelongsToVendor, HasAuditLog;

protected $fillable = [
        'vendor_id',
        'branch_id',
        'is_active',
        'name',
        'email',
        'phone',
        'address',
        'customer_type',
        'company_name',
        'tin',
        'loyalty_points',
        'credit_balance',
        'credit_limit',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'loyalty_points' => 'integer',
        'credit_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

// Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Organization\Models\Branch::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function washOrders(): HasMany
    {
        return $this->hasMany(WashOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    // Scopes
    public function scopeCorporate($query)
    {
        return $query->where('customer_type', 'corporate');
    }

    public function scopeIndividual($query)
    {
        return $query->where('customer_type', 'individual');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Helpers
    public function isCorporate(): bool
    {
        return $this->customer_type === 'corporate';
    }

    public function hasCredit(): bool
    {
        return $this->credit_limit > 0;
    }

    public function availableCredit(): float
    {
        return max(0, $this->credit_limit - $this->credit_balance);
    }

    public function canUseCredit(float $amount): bool
    {
        return $this->hasCredit() && $this->availableCredit() >= $amount;
    }

    public function addLoyaltyPoints(int $points): void
    {
        $this->increment('loyalty_points', $points);
    }

    public function deductLoyaltyPoints(int $points): bool
    {
        if ($this->loyalty_points >= $points) {
            $this->decrement('loyalty_points', $points);
            return true;
        }
        return false;
    }

    public function totalSpent(): float
    {
        return $this->invoices()->where('status', 'paid')->sum('total');
    }

    public function pendingBalance(): float
    {
        return $this->invoices()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum('balance_due');
    }
}
