<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPaymentAttempt extends Model
{
    protected $guarded = [];

    protected $casts = ['amount' => 'decimal:2', 'confirmed_at' => 'datetime'];

    public function invoice()
    {
        return $this->belongsTo(VendorPlatformInvoice::class, 'invoice_id');
    }
}
