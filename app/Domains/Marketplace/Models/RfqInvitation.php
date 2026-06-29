<?php

namespace App\Domains\Marketplace\Models;

use App\Domains\Platform\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqInvitation extends Model
{
    protected $fillable = ['rfq_id', 'supplier_vendor_id', 'status'];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'supplier_vendor_id');
    }
}
