<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'branch_id',
        'employment_type',
        'job_title',
        'department',
        'hired_at',
        'terminated_at',
        'termination_reason',
        'probation_end_at',
        'skill_level',
        'specializations',
        'certifications',
        'hourly_rate',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
