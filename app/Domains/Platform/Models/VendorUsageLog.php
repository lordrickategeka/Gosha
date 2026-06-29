<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorUsageLog extends Model
{
    protected $fillable = [
        'vendor_id',
        'branch_id',
        'metric',
        'quantity',
        'amount',
        'reference_type',
        'reference_id',
        'usage_date',
        'billing_period',
        'billed',
        'platform_invoice_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'amount' => 'decimal:2',
        'usage_date' => 'date',
        'billed' => 'boolean',
    ];

    const METRIC_WORK_ORDER = 'work_order';
    const METRIC_WASH_ORDER = 'wash_order';
    const METRIC_INVOICE = 'invoice';
    const METRIC_PAYMENT_RECEIVED = 'payment_received';
    const METRIC_SMS_SENT = 'sms_sent';
    const METRIC_USER_ADDED = 'user_added';
    const METRIC_BRANCH_ADDED = 'branch_added';
    const METRIC_CUSTOMER_ADDED = 'customer_added';
    const METRIC_STORAGE_USED = 'storage_used';

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function platformInvoice(): BelongsTo
    {
        return $this->belongsTo(PlatformInvoice::class);
    }

    // Scopes
    public function scopeUnbilled($query)
    {
        return $query->where('billed', false);
    }

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('billing_period', $period);
    }

    public function scopeForMetric($query, string $metric)
    {
        return $query->where('metric', $metric);
    }

    // Static helpers
    public static function logUsage(
        int $vendorId,
        string $metric,
        float $quantity = 1,
        float $amount = 0,
        ?int $branchId = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): self {
        return self::create([
            'vendor_id' => $vendorId,
            'branch_id' => $branchId,
            'metric' => $metric,
            'quantity' => $quantity,
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'usage_date' => now()->toDateString(),
            'billing_period' => now()->format('Y-m'),
            'billed' => false,
        ]);
    }

    public static function getUsageSummary(int $vendorId, string $period): array
    {
        return self::where('vendor_id', $vendorId)
            ->where('billing_period', $period)
            ->selectRaw('metric, SUM(quantity) as total_quantity, SUM(amount) as total_amount')
            ->groupBy('metric')
            ->get()
            ->keyBy('metric')
            ->toArray();
    }
}
