<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use Livewire\Component;

class CreateAppointmentsComponent extends Component
{
    public $customer_id = '';
    public $vehicle_id = '';
    public $customerSearch = '';
    public $showCustomerDropdown = false;
    public $type = 'service';
    public $scheduled_date;
    public $scheduled_time = '09:00';
    public $estimated_duration = 60;
    public $notes = '';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'vehicle_id' => 'required|exists:vehicles,id',
        'type' => 'required|in:service,repair,wash,diagnostics,other',
        'scheduled_date' => 'required|date|after_or_equal:today',
        'scheduled_time' => 'required',
        'estimated_duration' => 'required|integer|min:15',
    ];

    public function mount()
    {
        $this->scheduled_date = now()->addDay()->format('Y-m-d');
    }

    public function updatedCustomerSearch()
    {
        $this->showCustomerDropdown = strlen($this->customerSearch) >= 2;
    }

    public function selectCustomer($id)
    {
        $customer = Customer::find($id);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customerSearch = $customer->name . ' - ' . $customer->phone;
            $this->showCustomerDropdown = false;
        }
    }

    public function getCustomersProperty()
    {
        if (strlen($this->customerSearch) < 2) return collect();
        return Customer::where('name', 'like', "%{$this->customerSearch}%")
            ->orWhere('phone', 'like', "%{$this->customerSearch}%")
            ->limit(10)->get();
    }

    public function getVehiclesProperty()
    {
        if (!$this->customer_id) return collect();
        return Vehicle::where('customer_id', $this->customer_id)->get();
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
            'estimated_duration' => $this->estimated_duration,
            'status' => 'scheduled',
            'notes' => $this->notes,
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
