<?php

namespace App\Models;

use App\Models\JobCard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceJob extends JobCard
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'technician_id',
        'description',
        'status',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the vehicle that this service job belongs to.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the technician (user) assigned to this service job.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the invoice generated for this service job.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
