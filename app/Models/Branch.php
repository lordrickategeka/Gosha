<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes, BelongsToVendor, HasAuditLog;

    protected $fillable = [
        'vendor_id',
        'name',
        'address',
        'phone',
        'email',
        'is_active',
        'is_main',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main' => 'boolean',
    ];

    // Relationships
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function serviceBays(): HasMany
    {
        return $this->hasMany(ServiceBay::class);
    }

    public function washBays(): HasMany
    {
        return $this->hasMany(WashBay::class);
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

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function availableServiceBays()
    {
        return $this->serviceBays()->where('status', 'available')->get();
    }

    public function availableWashBays()
    {
        return $this->washBays()->where('status', 'available')->get();
    }

    public function todayWorkOrders()
    {
        return $this->workOrders()->whereDate('created_at', today())->get();
    }

    public function todayWashOrders()
    {
        return $this->washOrders()->whereDate('created_at', today())->get();
    }
}
