<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'job_card_id', 'vehicle_type_id', 'service_type_id',
        'commission_amount', 'status', 'commission_date'
    ];

    protected $casts = [
        'commission_amount' => 'decimal:2',
        'commission_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
