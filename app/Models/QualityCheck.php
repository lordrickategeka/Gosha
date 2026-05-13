<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToVendor;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityCheck extends Model
{
    use HasFactory, BelongsToVendor, BelongsToBranch, HasAuditLog;

    protected $fillable = [
        'work_order_id',
        'vehicle_id',
        'customer_id',
        'vendor_id',
        'branch_id',
        'inspector_user_id',
        'inspection_date',
        'status',
        'general_notes',
        'signed_file_path',
        'completed_at',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the work order
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the vehicle
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vendor
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the inspector
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_user_id');
    }

    /**
     * Get quality check items
     */
    public function items(): HasMany
    {
        return $this->hasMany(QualityCheckItem::class);
    }

    /**
     * Scope: Pending quality checks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Completed quality checks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Passed quality checks
     */
    public function scopePassed($query)
    {
        return $query->where('status', 'passed');
    }

    /**
     * Scope: Quality checks with issues
     */
    public function scopeHasIssues($query)
    {
        return $query->where('status', 'has_issues');
    }

    /**
     * Check if quality check has critical issues
     */
    public function hasCriticalIssues(): bool
    {
        return $this->items()
            ->where('status', 'needs_attention')
            ->exists();
    }

    /**
     * Get items that need attention
     */
    public function getIssuesAttribute()
    {
        return $this->items()
            ->where('status', 'needs_attention')
            ->get();
    }

    /**
     * Mark quality check as completed
     */
    public function markAsCompleted(): void
    {
        $hasIssues = $this->hasCriticalIssues();

        $this->update([
            'status' => $hasIssues ? 'has_issues' : 'passed',
            'completed_at' => now(),
        ]);

        // Update work order status
        if (!$hasIssues) {
            $this->workOrder->update(['status' => 'ready']);
        }
    }

    /**
     * Check if quality check is editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'has_issues']);
    }

    /**
     * Get grouped items by section
     */
    public function getItemsBySection()
    {
        return $this->items()->get()->groupBy('section');
    }
}
