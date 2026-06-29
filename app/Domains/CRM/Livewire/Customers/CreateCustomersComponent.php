<?php

namespace App\Domains\CRM\Livewire\Customers;

use App\Domains\Organization\Models\Branch;
use App\Domains\CRM\Models\Customer;
use Livewire\Component;

class CreateCustomersComponent extends Component
{
    public $name = '';
    public $phone = '';
    public $email = '';
    public $company = '';
    public $address = '';
    public $tax_id = '';
    public $notes = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'nullable|email|max:255',
        'company' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:500',
        'tax_id' => 'nullable|string|max:50',
        'notes' => 'nullable|string|max:1000',
    ];

    public function save()
    {
        $this->validate();

        $branchVendorId = Branch::where('id', session('current_branch_id'))->value('vendor_id');
        $vendorId = $branchVendorId ?: auth()->user()->vendor_id;

        if (!$vendorId) {
            $this->addError('name', 'Unable to determine vendor for this customer. Please select a branch and try again.');
            return;
        }

        $customer = Customer::create([
            'vendor_id' => $vendorId,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'company' => $this->company,
            'address' => $this->address,
            'tax_id' => $this->tax_id,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Customer created successfully.');
        return redirect()->route('customers.show', $customer);
    }

    public function render()
    {
        return view('livewire.customers.create-customers-component')
            ->layout('components.layouts.app', ['title' => 'Add Customer']);
    }
}
