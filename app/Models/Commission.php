<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'user_id',
        'branch_id',
        'commission_rule_id',
        'reference_type',
        'reference_id',
        'base_amount',
        'commission_amount',
        'status',
        'approved_by',
        'approved_at',
        'paid_at',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Polymorphic reference
    public function reference()
    {
        return match ($this->reference_type) {
            'work_order' => $this->belongsTo(WorkOrder::class, 'reference_id'),
            'wash_order' => $this->belongsTo(WashOrder::class, 'reference_id'),
            'invoice' => $this->belongsTo(Invoice::class, 'reference_id'),
            default => null,
        };
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function approve(?User $approver = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver?->id ?? auth()->id(),
            'approved_at' => now(),
        ]);
    }

    public function markAsPaid(?string $reference = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $reference,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'cancelled' => 'error',
            default => 'ghost',
        };
    }

    public function getReferenceDisplayAttribute(): string
    {
        $ref = $this->reference;
        
        return match ($this->reference_type) {
            'work_order' => 'WO: ' . ($ref?->order_number ?? $this->reference_id),
            'wash_order' => 'Wash: ' . ($ref?->order_number ?? $this->reference_id),
            'invoice' => 'Inv: ' . ($ref?->invoice_number ?? $this->reference_id),
            default => $this->reference_type . '#' . $this->reference_id,
        };
    }

    // Static helpers
    public static function createFromWorkOrder(WorkOrder $workOrder, User $technician, CommissionRule $rule): ?self
    {
        $baseAmount = match ($rule->applies_to) {
            'labor' => $workOrder->labor_total,
            'parts' => $workOrder->parts_total,
            'total' => $workOrder->subtotal,
            default => 0,
        };

        $commissionAmount = $rule->calculateCommission($baseAmount);

        if ($commissionAmount <= 0) {
            return null;
        }

        return static::create([
            'user_id' => $technician->id,
            'branch_id' => $workOrder->branch_id,
            'commission_rule_id' => $rule->id,
            'reference_type' => 'work_order',
            'reference_id' => $workOrder->id,
            'base_amount' => $baseAmount,
            'commission_amount' => $commissionAmount,
            'status' => 'pending',
        ]);
    }

    public static function createFromWashOrder(WashOrder $washOrder, User $attendant, CommissionRule $rule): ?self
    {
        $baseAmount = $washOrder->subtotal;
        $commissionAmount = $rule->calculateCommission($baseAmount);

        if ($commissionAmount <= 0) {
            return null;
        }

        return static::create([
            'user_id' => $attendant->id,
            'branch_id' => $washOrder->branch_id,
            'commission_rule_id' => $rule->id,
            'reference_type' => 'wash_order',
            'reference_id' => $washOrder->id,
            'base_amount' => $baseAmount,
            'commission_amount' => $commissionAmount,
            'status' => 'pending',
        ]);
    }
}
