<?php
namespace App\Domains\Vehicles\Models;

use App\Domains\CRM\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'customer_id',
        'job_card_id',
        'item_name',
        'description',
        'quantity',
        'part_number',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
