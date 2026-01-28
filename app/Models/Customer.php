<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'phone',
        'customer_name',
        'company_name',
        'email',
        'contact_person',
        'address',
        'nature_of_customer'
    ];
    public function vehicleItems()
    {
        return $this->hasMany(VehicleItem::class);
    }

    public function jobCards(): HasMany
    {
        return $this->hasMany(JobCard::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    // Find customer by phone or email
    public static function findByIdentifier($phone = null, $email = null)
    {
        $query = static::query();

        if ($phone) {
            $query->orWhere('phone', $phone);
        }

        if ($email) {
            $query->orWhere('email', $email);
        }

        return $query->first();
    }
}
