<?php

namespace App\Livewire;


use Livewire\Component;
use App\Models\ServiceType;

class ServiceTypesComponent extends Component
{
    public $serviceTypes;
    public $name, $description, $price, $estimated_duration, $is_active = true;
    public $editMode = false;
    public $serviceTypeId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'estimated_duration' => 'required|integer|min:1',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->refreshServiceTypes();
    }

    public function refreshServiceTypes()
    {
        $this->serviceTypes = ServiceType::orderBy('name')->get();
    }

    public function create()
    {
        $validated = $this->validate();
        ServiceType::create($validated);
        $this->resetForm();
        $this->refreshServiceTypes();
        session()->flash('message', 'Service type created successfully.');
    }

    public function edit($id)
    {
        $type = ServiceType::findOrFail($id);
        $this->serviceTypeId = $type->id;
        $this->name = $type->name;
        $this->description = $type->description;
        $this->price = $type->price;
        $this->estimated_duration = $type->estimated_duration;
        $this->is_active = $type->is_active;
        $this->editMode = true;
    }

    public function update()
    {
        $validated = $this->validate();
        $type = ServiceType::findOrFail($this->serviceTypeId);
        $type->update($validated);
        $this->resetForm();
        $this->refreshServiceTypes();
        session()->flash('message', 'Service type updated successfully.');
    }

    public function confirmDelete($id)
    {
        $this->serviceTypeId = $id;
    }

    public function delete()
    {
        if ($this->serviceTypeId) {
            ServiceType::destroy($this->serviceTypeId);
            $this->resetForm();
            $this->refreshServiceTypes();
            session()->flash('message', 'Service type deleted successfully.');
        }
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'price', 'estimated_duration', 'is_active', 'editMode', 'serviceTypeId']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.service-types-component', [
            'serviceTypes' => $this->serviceTypes,
        ])->layout('components.layouts.app', ['title' => 'Service Types']);
    }
}
