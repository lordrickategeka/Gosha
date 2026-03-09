<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'job_card_id', 'subtotal', 'tax_amount',
        'discount_amount', 'total_amount', 'status', 'due_date'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $invoice->invoice_number = 'INV' . date('Ymd') . str_pad(static::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);
        });
    }

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function serviceJob()
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments->sum('amount_paid');
    }

    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->total_paid;
    }
}
