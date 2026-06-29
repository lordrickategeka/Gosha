<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBillingConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'billing_model',
        'subscription_amount',
        'subscription_interval',
        'transaction_fee_percent',
        'transaction_fee_flat',
        'commission_percent',
        'next_billing_date',
    ];

    protected $casts = [
        'subscription_amount' => 'decimal:2',
        'transaction_fee_percent' => 'decimal:2',
        'transaction_fee_flat' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'next_billing_date' => 'date',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function calculatePlatformFee(float $invoiceAmount): float
    {
        return match ($this->billing_model) {
            'transaction_fee' => $this->calculateTransactionFee($invoiceAmount),
            'commission_cut' => $invoiceAmount * ($this->commission_percent / 100),
            'hybrid' => $this->calculateTransactionFee($invoiceAmount),
            default => 0,
        };
    }

    protected function calculateTransactionFee(float $amount): float
    {
        $percentFee = $amount * (($this->transaction_fee_percent ?? 0) / 100);
        $flatFee = $this->transaction_fee_flat ?? 0;
        return $percentFee + $flatFee;
    }
}
