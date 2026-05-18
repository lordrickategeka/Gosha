<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id', 'name', 'description', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
