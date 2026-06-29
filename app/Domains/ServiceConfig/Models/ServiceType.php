<?php
namespace App\Domains\ServiceConfig\Models;

use App\Domains\Commissions\Models\CommissionPriceList;
use App\Domains\Operations\Models\JobCard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price', 'estimated_duration'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'estimated_duration' => 'integer',
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
