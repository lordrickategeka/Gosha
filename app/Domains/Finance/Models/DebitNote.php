<?php

namespace App\Domains\Finance\Models;

use App\Domains\CRM\Models\Customer;
use App\Domains\Operations\Models\WorkOrder;
use App\Models\User;
use App\Shared\Traits\BelongsToBranch;
use App\Shared\Traits\GeneratesOrderNumber;
use App\Shared\Traits\HasAuditLog;
use App\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DebitNote extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, HasAuditLog, GeneratesOrderNumber, HasUuid;

    protected $orderNumberField = 'debit_note_number';
    protected $orderNumberPrefix = 'DN';

    protected $fillable = [
        'branch_id',
        'work_order_id',
        'customer_id',
        'invoice_id',
        'quotation_id',
        'created_by',
        'debit_note_number',
        'approval_token',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'notes',
        'sent_at',
        'responded_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $debitNote) {
            if (empty($debitNote->approval_token)) {
                $debitNote->approval_token = (string) Str::uuid();
            }
        });

        static::saving(function (self $debitNote) {
            $debitNote->recalculateTotals();
        });
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DebitNoteItem::class)->orderBy('sort_order');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPartiallyApproved(): bool
    {
        return $this->status === 'partially_approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isApplied(): bool
    {
        return $this->status === 'applied';
    }

    public function canBeRespondedTo(): bool
    {
        return in_array($this->status, ['sent']);
    }

    public function approvalUrl(): string
    {
        return route('debit-notes.public', $this->approval_token);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total') ?: $this->subtotal;
        $taxAmount = $subtotal * (($this->tax_rate ?? 0) / 100);
        $total = $subtotal + $taxAmount - ($this->discount_amount ?? 0);

        $this->subtotal = round($subtotal, 2);
        $this->tax_amount = round($taxAmount, 2);
        $this->total = round($total, 2);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'neutral',
            'sent' => 'info',
            'partially_approved' => 'warning',
            'approved' => 'success',
            'rejected' => 'error',
            'applied' => 'success',
            default => 'neutral',
        };
    }
}
