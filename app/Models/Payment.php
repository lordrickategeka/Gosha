<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog;

    protected $fillable = [
        'invoice_id',
        'received_by',
        'amount',
        'payment_method',
        'provider',
        'reference',
        'receipt_number',
        'status',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->receipt_number) {
                $payment->receipt_number = static::generateReceiptNumber();
            }
            if (!$payment->payment_date) {
                $payment->payment_date = today();
            }
        });

        static::created(function ($payment) {
            // Recalculate invoice totals after payment
            $payment->invoice->recalculateTotals();
            $payment->invoice->saveQuietly();
        });
    }

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year);
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    // Helpers
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    public function isMobileMoney(): bool
    {
        return $this->payment_method === 'mobile_money';
    }

    public static function generateReceiptNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return sprintf('RCP-%s-%04d', $date, $count);
    }

    public function getMethodDisplayAttribute(): string
    {
        $display = match ($this->payment_method) {
            'cash' => 'Cash',
            'mobile_money' => 'Mobile Money',
            'card' => 'Card',
            'bank_transfer' => 'Bank Transfer',
            'credit' => 'Credit',
            'cheque' => 'Cheque',
            default => ucfirst($this->payment_method),
        };

        if ($this->provider) {
            $display .= " ({$this->provider})";
        }

        return $display;
    }
}
