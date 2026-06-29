<?php

namespace App\Domains\CRM\Livewire\Customers;

use App\Domains\CRM\Models\Customer;
use Livewire\Component;

class ShowCustomersComponent extends Component
{
    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer->load(['vehicles', 'workOrders' => fn($q) => $q->latest()->take(5), 'washOrders' => fn($q) => $q->latest()->take(5), 'invoices' => fn($q) => $q->latest()->take(5)]);
    }

    public function render()
    {
        return view('livewire.customers.show-customers-component')
            ->layout('components.layouts.app', ['title' => $this->customer->name]);
    }
}
