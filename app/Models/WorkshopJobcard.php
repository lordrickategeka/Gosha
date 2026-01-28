<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkshopJobcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'jobcard_id',
        'material_id',
        'material_name',
        'quantity',
        'notes',
        'additional_works',
    ];

    protected $casts = [
        'additional_works' => 'array',
    ];
    public function jobcard()
    {
        return $this->belongsTo(JobCard::class, 'jobcard_id');
    }

    public function mechanicalWorks()
    {
        return $this->hasMany(MechanicalWork::class, 'workshop_jobcard_id');
    }
    // Optionally, add relation to Material/Inventory if exists
    // public function material()
    // {
    //     return $this->belongsTo(Material::class, 'material_id');
    // }
}

/*
we need to have these fields on the workshop_jobcards table:
-job_card_id (foreign key to job_cards table) -> this will be the selected job card for which the workshop jobcard is being created
-mechanical_works_id (foreign key to mechanical_works table, this is updated when a mechanical work is added on the workshop jobcard)
** the user can add multiple mechanical works to a single workshop jobcard, so this field is used to reference the specific mechanical work being added.
** the user enters the mechanical work details (service type, repair items, quantity, notes) and when they save/add that mechanical work, this field is updated to reference that mechanical work.

-additional_works

for mechanical_works table, when a mechanical work is added to the workshop jobcard, this field is updated to reference that mechanical work.
-mechanical_works
-service_type_id (foreign key to service_types table) --> update with the selected service type for that mechanical work on the workshop jobcard
-repair_items_id (foreign key to materials/inventory table, if selected from inventory)
-repair_items (json field, for custom/manual entry) -> this can be a json field to store multiple items if needed
-quantity (integer)
-notes (text, nullable)

-workshop_jobcard_id (foreign key to workshop_jobcards table)

*/
