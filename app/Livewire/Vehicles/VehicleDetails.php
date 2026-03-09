<?php

namespace App\Livewire\Vehicles;

use Livewire\Component;
use App\Models\Vehicle;
use App\Models\JobCard;
use App\Models\WorkshopJobcard;

class VehicleDetails extends Component
{
    public $vehicle;
    public $jobCards;
    public $workshopJobcards;

    public function mount($vehicleId)
    {
        $this->vehicle = Vehicle::with('customer')->find($vehicleId);

        if (!$this->vehicle) {
            session()->flash('error', 'Vehicle not found.');
            return redirect()->route('vehicles.index');
        }

        $this->jobCards = $this->vehicle->jobCards;
        $this->workshopJobcards = WorkshopJobcard::whereIn('jobcard_id', $this->jobCards->pluck('id'))->get();
    }

    public function render()
    {
        return view('livewire.vehicles.vehicle-details', [
            'vehicle' => $this->vehicle,
        ]);
    }
}
