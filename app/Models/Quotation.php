<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\GeneratesOrderNumber;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, HasAuditLog, GeneratesOrderNumber;

    protected $orderNumberField  = 'quotation_number';
    protected $orderNumberPrefix = 'QUO';

    protected $fillable = [
        'branch_id',
        'work_order_id',
        'customer_id',
        'created_by',
        'approved_by_user_id',
        'parent_quotation_id',
        'quotation_number',
        'version',
        'status',
        'approval_token',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'discount_amount',
        'total',
        'notes',
        'terms_and_conditions',
        'valid_until',
        'sent_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'vat_rate'        => 'decimal:2',
        'vat_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'           => 'decimal:2',
        'valid_until'     => 'date',
        'sent_at'         => 'datetime',
        'approved_at'     => 'datetime',
        'rejected_at'     => 'datetime',
        'version'         => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->approval_token)) {
                $quotation->approval_token = (string) Str::uuid();
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function parentQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Quotation::class, 'parent_quotation_id')->orderBy('version');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    // ─── Status Helpers ───────────────────────────────────────────────────

    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isSent(): bool      { return $this->status === 'sent'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isExpired(): bool   { return $this->status === 'expired'; }

    public function canBeApproved(): bool
    {
        if ($this->isApproved() || $this->isExpired()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return in_array($this->status, ['sent', 'draft']);
    }

    // ─── URL Helper ───────────────────────────────────────────────────────

    public function approvalUrl(): string
    {
        return route('quotations.public', $this->approval_token);
    }

    public function whatsappShareUrl(): string
    {
        $message = urlencode(
            "Hello {$this->customer->name},\n\n" .
            "Please review your quotation {$this->quotation_number} here:\n" .
            $this->approvalUrl()
        );

        return "https://wa.me/{$this->customer->phone}?text={$message}";
    }

    // ─── Totals Recalculation ─────────────────────────────────────────────

    public function recalculateTotals(): void
    {
        $items = $this->items()->get();

        $subtotal = $items->sum('total'); // sum of (qty * price - discount) per line
        $vatAmount = $items->filter(fn($i) => $i->vat_applicable)
            ->sum(fn($i) => $i->total * ($i->vat_rate / 100));

        $this->update([
            'subtotal'   => $subtotal,
            'vat_amount' => round($vatAmount, 2),
            'total'      => round($subtotal + $vatAmount, 2),
        ]);
    }

    // ─── Status Color ─────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'    => 'neutral',
            'sent'     => 'info',
            'approved' => 'success',
            'rejected' => 'error',
            'expired'  => 'warning',
            default    => 'neutral',
        };
    }
}
