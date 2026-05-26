<?php
namespace App\Models;

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
