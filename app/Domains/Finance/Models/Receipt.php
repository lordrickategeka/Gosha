<?php
namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id', 'receipt_number', 'issued_at'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            $receipt->receipt_number = 'RCP' . date('Ymd') . str_pad(static::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);
        });
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
