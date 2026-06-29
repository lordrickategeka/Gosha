<?php

namespace App\Domains\Platform\Models;

use App\Domains\Finance\Models\Invoice;
use App\Shared\Traits\GeneratesOrderNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformInvoice extends Model
{
    use HasFactory, GeneratesOrderNumber;

    protected $orderNumberField = 'invoice_number';
    protected $orderNumberPrefix = 'PLAT';

    protected $fillable = [
        'vendor_id',
        'invoice_number',
        'amount',
        'period_start',
        'period_end',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                  ->where('period_end', '<', today()->subDays(14));
            });
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' ||
            ($this->status === 'pending' && $this->period_end < today()->subDays(14));
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function markAsOverdue(): void
    {
        $this->update(['status' => 'overdue']);
    }

    public function getPeriodDisplayAttribute(): string
    {
        return $this->period_start->format('M d') . ' - ' . $this->period_end->format('M d, Y');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'paid' => 'success',
            'overdue' => 'error',
            default => 'ghost',
        };
    }

    // Generate invoice for a vendor based on their billing config
    public static function generateForVendor(Vendor $vendor, \Carbon\Carbon $periodStart, \Carbon\Carbon $periodEnd): ?self
    {
        $billingConfig = $vendor->billingConfig;

        if (!$billingConfig || $billingConfig->billing_model === 'none') {
            return null;
        }

        $amount = 0;

        switch ($billingConfig->billing_model) {
            case 'subscription':
                $amount = $billingConfig->subscription_amount;
                break;

            case 'transaction_fee':
            case 'commission_cut':
            case 'hybrid':
                // Calculate based on invoices in the period
                $vendorInvoices = Invoice::whereHas('branch', function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                })
                    ->whereBetween('issue_date', [$periodStart, $periodEnd])
                    ->where('status', 'paid')
                    ->get();

                $totalRevenue = $vendorInvoices->sum('total');
                $amount = $billingConfig->calculatePlatformFee($totalRevenue);

                // Add subscription if hybrid
                if ($billingConfig->billing_model === 'hybrid') {
                    $amount += $billingConfig->subscription_amount ?? 0;
                }
                break;
        }

        if ($amount <= 0) {
            return null;
        }

        return static::create([
            'vendor_id' => $vendor->id,
            'amount' => $amount,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => 'pending',
        ]);
    }
}
