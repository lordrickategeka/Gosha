<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'vendor_id',
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'is_platform_user',
        'is_active',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_user' => 'boolean',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryBranch()
    {
        return $this->branches()->wherePivot('is_primary', true)->first();
    }

    public function workOrdersAssigned(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'assigned_technician_id');
    }

    public function washOrdersAssigned(): HasMany
    {
        return $this->hasMany(WashOrder::class, 'assigned_attendant_id');
    }

    public function paymentsReceived(): HasMany
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    public function expensesRecorded(): HasMany
    {
        return $this->hasMany(Expense::class, 'recorded_by');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopePlatformUsers($query)
    {
        return $query->where('is_platform_user', true);
    }

    public function scopeVendorUsers($query)
    {
        return $query->where('is_platform_user', false)->whereNotNull('vendor_id');
    }

    // Helpers
    public function isPlatformUser(): bool
    {
        return $this->is_platform_user;
    }

    public function isVendorOwner(): bool
    {
        return $this->hasRole('vendor-owner');
    }

    public function isBranchManager(): bool
    {
        return $this->hasRole('branch-manager');
    }

    public function isTechnician(): bool
    {
        return $this->hasRole('technician');
    }

    public function isWashAttendant(): bool
    {
        return $this->hasRole('wash-attendant');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    public function canAccessBranch(Branch $branch): bool
    {
        if ($this->isPlatformUser()) {
            return true;
        }

        if ($this->isVendorOwner() && $branch->vendor_id === $this->vendor_id) {
            return true;
        }

        return $this->branches->contains($branch->id);
    }

    public function getAccessibleBranchIds(): array
    {
        if ($this->isPlatformUser()) {
            return Branch::pluck('id')->toArray();
        }

        if ($this->isVendorOwner()) {
            return Branch::where('vendor_id', $this->vendor_id)->pluck('id')->toArray();
        }

        return $this->branches->pluck('id')->toArray();
    }
}
