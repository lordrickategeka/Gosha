<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;

class EditVehiclesComponent extends Component
{
    public Vehicle $vehicle;
    public $registration_number = '';
    public $make = '';
    public $model = '';
    public $year = '';
    public $color = '';
    public $vin = '';
    public $engine_number = '';
    public $fuel_type = 'petrol';
    public $transmission = 'automatic';
    public $notes = '';

    protected $rules = [
        'registration_number' => 'required|string|max:20',
        'make' => 'nullable|string|max:50',
        'model' => 'nullable|string|max:50',
        'year' => 'nullable|integer|min:1900|max:2030',
        'color' => 'nullable|string|max:30',
        'vin' => 'nullable|string|max:50',
    ];

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
        $this->registration_number = $vehicle->registration_number;
        $this->make = $vehicle->make;
        $this->model = $vehicle->model;
        $this->year = $vehicle->year;
        $this->color = $vehicle->color;
        $this->vin = $vehicle->vin;
        $this->engine_number = $vehicle->engine_number;
        $this->fuel_type = $vehicle->fuel_type ?? 'petrol';
        $this->transmission = $vehicle->transmission ?? 'automatic';
        $this->notes = $vehicle->notes;
    }

    public function save()
    {
        $this->validate();

        $this->vehicle->update([
            'registration_number' => strtoupper($this->registration_number),
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year ?: null,
            'color' => $this->color,
            'vin' => $this->vin,
            'engine_number' => $this->engine_number,
            'fuel_type' => $this->fuel_type,
            'transmission' => $this->transmission,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Vehicle updated successfully.');
        return redirect()->route('vehicles.show', $this->vehicle);
    }

    public function render()
    {
        return view('livewire.vehicles.edit-vehicles-component')
            ->layout('components.layouts.app', ['title' => 'Edit Vehicle']);
    }
}
