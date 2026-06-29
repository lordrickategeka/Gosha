<?php

namespace App\Domains\Inventory\Models;

use App\Domains\Expenses\Models\Expense;
use App\Domains\Finance\Models\Bill;
use App\Domains\Finance\Models\BillPayment;
use App\Shared\Traits\BelongsToBranch;
use App\Shared\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, BelongsToVendor, BelongsToBranch;

    protected $fillable = [
        'vendor_id',
        'branch_id',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'notes',
        'payment_terms_days',
        'opening_balance',
        'current_balance',
        'credit_limit',
        'last_statement_at',
        'is_active',
    ];

    protected $casts = [
        'payment_terms_days' => 'integer',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'last_statement_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function billPayments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Helpers
    public function getTotalPurchasesAttribute(): float
    {
        return $this->inventoryMovements()
            ->where('movement_type', 'purchase')
            ->sum('total_cost');
    }

    public function getOutstandingPayablesAttribute(): float
    {
        return (float) $this->bills()
            ->whereIn('status', ['received', 'partially_paid', 'overdue'])
            ->sum('balance_due');
    }

    public function refreshCurrentBalance(): void
    {
        $this->update([
            'current_balance' => (float) $this->opening_balance + $this->outstanding_payables,
        ]);
    }
}
