<?php

namespace App\Domains\Vehicles\Livewire\Vehicles;

use App\Domains\Vehicles\Models\VehicleType;
use Livewire\Component;

class VehicleTypesComponent extends Component
{
    public $vehicleTypes;
    public $name;
    public $description;
    public $base_price;
    public $is_active = true;
    public $vehicleTypeId;
    public $editMode = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'base_price' => 'required|numeric|min:0',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->fetchVehicleTypes();
    }

    public function fetchVehicleTypes()
    {
        $this->vehicleTypes = VehicleType::orderBy('name')->get();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->base_price = '';
        $this->is_active = true;
        $this->vehicleTypeId = null;
        $this->editMode = false;
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->validate();
        VehicleType::create([
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'is_active' => $this->is_active,
        ]);
        $this->resetForm();
        $this->fetchVehicleTypes();
        session()->flash('message', 'Vehicle type created successfully!');
    }

    public function edit($id)
    {
        $type = VehicleType::findOrFail($id);
        $this->vehicleTypeId = $type->id;
        $this->name = $type->name;
        $this->description = $type->description;
        $this->base_price = $type->base_price;
        $this->is_active = $type->is_active;
        $this->editMode = true;
    }

    public function update()
    {
        $this->validate();
        $type = VehicleType::findOrFail($this->vehicleTypeId);
        $type->update([
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'is_active' => $this->is_active,
        ]);
        $this->resetForm();
        $this->fetchVehicleTypes();
        session()->flash('message', 'Vehicle type updated successfully!');
    }

    public function confirmDelete($id)
    {
        $this->vehicleTypeId = $id;
    }

    public function delete()
    {
        $type = VehicleType::findOrFail($this->vehicleTypeId);
        $type->delete();
        $this->resetForm();
        $this->fetchVehicleTypes();
        session()->flash('message', 'Vehicle type deleted successfully!');
    }


    public function render()
    {
        return view('livewire.vehicles.vehicle-types-component');
    }
}
