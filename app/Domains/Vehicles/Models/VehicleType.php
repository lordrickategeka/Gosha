<?php
namespace App\Domains\Vehicles\Models;

use App\Domains\Commissions\Models\CommissionPriceList;
use App\Domains\Operations\Models\JobCard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'base_price', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
    ];

    public function jobCards()
    {
        return $this->hasMany(JobCard::class);
    }

    public function commissionPriceLists()
    {
        return $this->hasMany(CommissionPriceList::class);
    }
}
