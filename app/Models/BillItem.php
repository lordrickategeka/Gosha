<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (BillItem $item) {
            $lineSubtotal = ($item->quantity ?? 0) * ($item->unit_price ?? 0);
            $item->total = $lineSubtotal + ($item->tax ?? 0) - ($item->discount ?? 0);
        });
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
