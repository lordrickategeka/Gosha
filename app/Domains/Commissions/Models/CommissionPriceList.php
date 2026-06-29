<?php
namespace App\Domains\Commissions\Models;

use App\Domains\ServiceConfig\Models\ServiceType;
use App\Domains\Vehicles\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type_id', 'service_type_id', 'commission_amount',
        'commission_percentage', 'is_active'
    ];

    protected $casts = [
        'commission_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
