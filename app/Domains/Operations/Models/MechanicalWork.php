<?php

namespace App\Domains\Operations\Models;

use App\Domains\ServiceConfig\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MechanicalWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_jobcard_id',
        'service_type_id',
        'repair_items_id',
        'repair_items',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'repair_items' => 'array',
    ];

    public function workshopJobcard()
    {
        return $this->belongsTo(WorkshopJobcard::class, 'workshop_jobcard_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    // Optional relation to a materials/inventory item
    // public function repairItem()
    // {
    //     return $this->belongsTo(Material::class, 'repair_items_id');
    // }
}
