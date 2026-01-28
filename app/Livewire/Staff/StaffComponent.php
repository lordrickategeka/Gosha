<?php

namespace App\Livewire\Staff;

use Livewire\Component;

use App\Models\Staff;

class StaffComponent extends Component
{
    public $staffList;
    public $name, $phone, $email, $role, $is_active = true, $base_salary;
    public $editMode = false;
    public $staffId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20|unique:staff,phone',
        'email' => 'nullable|email|unique:staff,email',
        'role' => 'required|in:washer,attendant,manager,admin',
        'is_active' => 'boolean',
        'base_salary' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->refreshStaffList();
    }

    public function refreshStaffList()
    {
        $this->staffList = Staff::orderBy('name')->get();
    }

    public function create()
    {
        $validated = $this->validate();
        Staff::create($validated);
        $this->resetForm();
        $this->refreshStaffList();
        session()->flash('message', 'Staff created successfully.');
    }

    public function edit($id)
    {
        $staff = Staff::findOrFail($id);
        $this->staffId = $staff->id;
        $this->name = $staff->name;
        $this->phone = $staff->phone;
        $this->email = $staff->email;
        $this->role = $staff->role;
        $this->is_active = $staff->is_active;
        $this->base_salary = $staff->base_salary;
        $this->editMode = true;
    }

    public function update()
    {
        $rules = $this->rules;
        $rules['phone'] .= ','.$this->staffId;
        $rules['email'] .= ','.$this->staffId;
        $validated = $this->validate($rules);
        $staff = Staff::findOrFail($this->staffId);
        $staff->update($validated);
        $this->resetForm();
        $this->refreshStaffList();
        session()->flash('message', 'Staff updated successfully.');
    }

    public function confirmDelete($id)
    {
        $this->staffId = $id;
    }

    public function delete()
    {
        if ($this->staffId) {
            Staff::destroy($this->staffId);
            $this->resetForm();
            $this->refreshStaffList();
            session()->flash('message', 'Staff deleted successfully.');
        }
    }

    public function resetForm()
    {
        $this->reset(['name', 'phone', 'email', 'role', 'is_active', 'base_salary', 'editMode', 'staffId']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.staff.staff-component', [
            'staffList' => $this->staffList,
        ]);
    }
}
