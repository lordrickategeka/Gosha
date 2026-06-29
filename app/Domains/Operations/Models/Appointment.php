<?php

namespace App\Domains\Operations\Models;

use App\Domains\CRM\Models\Customer;
use App\Domains\Vehicles\Models\Vehicle;
use App\Models\User;
use App\Shared\Traits\BelongsToBranch;
use App\Shared\Traits\GeneratesOrderNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, GeneratesOrderNumber;

    protected $orderNumberField = 'appointment_number';
    protected $orderNumberPrefix = 'APT';

    protected $fillable = [
        'branch_id',
        'customer_id',
        'vehicle_id',
        'created_by',
        'assigned_to',
        'appointment_number',
        'type',
        'scheduled_date',
        'scheduled_time',
        'duration_minutes',
        'status',
        'service_notes',
        'internal_notes',
        'reminder_sent',
        'confirmed_at',
        'checked_in_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
        'duration_minutes' => 'integer',
        'reminder_sent' => 'boolean',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    public function scopeTomorrow($query)
    {
        return $query->whereDate('scheduled_date', today()->addDay());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', today())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time');
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('scheduled_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeNeedsReminder($query)
    {
        return $query->where('reminder_sent', false)
            ->where('scheduled_date', today()->addDay())
            ->whereIn('status', ['scheduled', 'confirmed']);
    }

    // Helpers
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isToday(): bool
    {
        return $this->scheduled_date->isToday();
    }

    public function isPast(): bool
    {
        return $this->scheduled_date->isPast() && !$this->isToday();
    }

    public function canCheckIn(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']) && $this->isToday();
    }

    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function checkIn(): void
    {
        $this->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);
    }

    public function markNoShow(): void
    {
        $this->update(['status' => 'no_show']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function reschedule(string $date, string $time): void
    {
        $this->update([
            'scheduled_date' => $date,
            'scheduled_time' => $time,
            'status' => 'rescheduled',
            'reminder_sent' => false,
        ]);
    }

    public function complete(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markReminderSent(): void
    {
        $this->update(['reminder_sent' => true]);
    }

    // Convert to work order
    public function createWorkOrder(): WorkOrder
    {
        return WorkOrder::create([
            'branch_id' => $this->branch_id,
            'vehicle_id' => $this->vehicle_id,
            'customer_id' => $this->customer_id,
            'type' => $this->type === 'diagnostics' ? 'diagnostics' : 'service',
            'status' => 'open',
            'is_combo' => $this->type === 'combo',
            'customer_notes' => $this->service_notes,
            'checked_in_at' => now(),
        ]);
    }

    public function createWashOrder(): WashOrder
    {
        return WashOrder::create([
            'branch_id' => $this->branch_id,
            'vehicle_id' => $this->vehicle_id,
            'customer_id' => $this->customer_id,
            'wash_type' => 'standard',
            'status' => 'queued',
            'source' => 'appointment',
            'notes' => $this->service_notes,
            'queued_at' => now(),
        ]);
    }

    public function getScheduledDateTimeAttribute(): \Carbon\Carbon
    {
        return $this->scheduled_date->setTimeFromTimeString($this->scheduled_time->format('H:i:s'));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'info',
            'confirmed' => 'primary',
            'checked_in' => 'warning',
            'in_progress' => 'warning',
            'completed' => 'success',
            'no_show' => 'error',
            'cancelled' => 'error',
            'rescheduled' => 'secondary',
            default => 'ghost',
        };
    }

    public function getTypeDisplayAttribute(): string
    {
        return match ($this->type) {
            'service' => 'Service',
            'wash' => 'Wash',
            'combo' => 'Service + Wash',
            'diagnostics' => 'Diagnostics',
            'estimate' => 'Estimate',
            default => ucfirst($this->type),
        };
    }
}
