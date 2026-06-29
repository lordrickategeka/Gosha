<?php

namespace App\Domains\HR\Livewire\Staff;

use App\Domains\HR\Models\EmploymentDetail;
use Livewire\Component;
use App\Domains\HR\Models\Staff;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateStaffComponent extends Component
{
    public $first_name;
    public $last_name;
    public $display_name;
    public $email;
    public $phone_number;
    public $staff_code;
    public $national_id;
    public $employee_id_number;
    public $date_of_birth;
    public $gender;
    public $branch_id;
    public $employment_type;
    public $job_title;
    public $department;
    public $hired_at;
    public $terminated_at;
    public $termination_reason;
    public $probation_end_at;
    public $skill_level;
    public $specializations;
    public $certifications;
    public $hourly_rate;

    protected $rules = [
        // Staff validation rules
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'display_name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:staff,email',
        'phone_number' => 'required|string|unique:staff,phone_number',
        'staff_code' => 'required|string|unique:staff,staff_code',
        'national_id' => 'nullable|string|max:255',
        'employee_id_number' => 'nullable|string|max:255',
        'date_of_birth' => 'nullable|date',
        'gender' => 'nullable|string|in:male,female,other',

        // EmploymentDetail validation rules
        'branch_id' => 'required|exists:branches,id',
        'employment_type' => 'required|in:full_time,part_time,contract',
        'job_title' => 'required|string|max:255',
        'department' => 'required|string|max:255',
        'hired_at' => 'nullable|date',
        'terminated_at' => 'nullable|date',
        'termination_reason' => 'nullable|string|max:255',
        'probation_end_at' => 'nullable|date',
        'skill_level' => 'nullable|in:beginner,intermediate,advanced,expert',
        'specializations' => 'nullable|array',
        'certifications' => 'nullable|array',
        'hourly_rate' => 'nullable|numeric',
    ];

    public function createStaff()
    {
        $this->validate();

        $staff = Staff::create([
            'id' => Str::uuid(),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'staff_code' => $this->staff_code,
            'national_id' => $this->national_id,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
        ]);

        EmploymentDetail::create([
            'staff_id' => $staff->id,
            'branch_id' => $this->branch_id,
            'employment_type' => $this->employment_type,
            'job_title' => $this->job_title,
            'department' => $this->department,
            'hired_at' => $this->hired_at,
            'terminated_at' => $this->terminated_at,
            'termination_reason' => $this->termination_reason,
            'probation_end_at' => $this->probation_end_at,
            'skill_level' => $this->skill_level,
            'specializations' => $this->specializations,
            'certifications' => $this->certifications,
            'hourly_rate' => $this->hourly_rate,
        ]);

        session()->flash('message', 'Staff and employment details created successfully!');
        return redirect()->route('staff.index');
    }

    public function render()
    {
        return view('livewire.staff.create-staff-component');
    }

    public function __invoke()
    {
        return $this->render();
    }
}
