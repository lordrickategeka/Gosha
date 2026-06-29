<?php

namespace App\Domains\CRM\Livewire\Appointments;

use App\Domains\Operations\Models\Appointment;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateAppointmentsComponent extends Component
{
    public $customer_id = '';
    public $vehicle_id = '';
    public $type = 'service';
    public $scheduled_date;
    public $scheduled_time = '09:00';
    public $duration_minutes = 60;
    public $notes = '';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'vehicle_id' => 'required|exists:vehicles,id',
        'type' => 'required|in:service,wash,combo,diagnostics,estimate',
        'scheduled_date' => 'required|date|after_or_equal:today',
        'scheduled_time' => 'required',
        'duration_minutes' => 'required|integer|min:15',
    ];

    public function mount()
    {
        $this->scheduled_date = now()->addDay()->format('Y-m-d');
    }

    #[On('customerSelected')]
    public function handleCustomerSelected($customerId)
    {
        $this->customer_id = $customerId;
        $this->vehicle_id = '';
    }

    #[On('vehicleSelected')]
    public function handleVehicleSelected($vehicleId)
    {
        $this->vehicle_id = $vehicleId;
    }

    public function save()
    {
        $this->validate();

        Appointment::create([
            'branch_id' => session('current_branch_id'),
            'customer_id' => $this->customer_id,
            'vehicle_id' => $this->vehicle_id,
            'created_by' => auth()->id(),
            'type' => $this->type,
            'scheduled_date' => $this->scheduled_date,
            'scheduled_time' => $this->scheduled_time,
            'duration_minutes' => $this->duration_minutes,
            'status' => 'scheduled',
            'service_notes' => $this->notes,
        ]);

        session()->flash('success', 'Appointment scheduled.');
        return redirect()->route('appointments.index');
    }

    public function render()
    {
        return view('livewire.appointments.create-appointments-component')
            ->layout('components.layouts.app', ['title' => 'New Appointment']);
    }
}
