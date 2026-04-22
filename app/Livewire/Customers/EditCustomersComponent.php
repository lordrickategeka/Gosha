<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;

class EditCustomersComponent extends Component
{
    public Customer $customer;
    public $name, $phone, $email, $company, $address, $tax_id, $notes;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'nullable|email|max:255',
        'company' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:500',
        'tax_id' => 'nullable|string|max:50',
        'notes' => 'nullable|string|max:1000',
    ];

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->company = $customer->company;
        $this->address = $customer->address;
        $this->tax_id = $customer->tax_id;
        $this->notes = $customer->notes;
    }

    public function save()
    {
        $this->validate();

        $this->customer->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'company' => $this->company,
            'address' => $this->address,
            'tax_id' => $this->tax_id,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Customer updated successfully.');
        return redirect()->route('customers.show', $this->customer);
    }

    public function render()
    {
        return view('livewire.customers.edit-customers-component')
            ->layout('components.layouts.app', ['title' => 'Edit Customer']);
    }
}
