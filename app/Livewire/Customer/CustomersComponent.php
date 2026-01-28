<?php

namespace App\Livewire\Customer;

use Livewire\Component;

class CustomersComponent extends Component
{
    public function render()
    {
        $customers = \App\Models\Customer::with(['vehicles', 'vehicleItems'])->get();
        return view('livewire.customer.customers-component', [
            'customers' => $customers,
        ]);
    }
}
