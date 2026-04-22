<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $customers = Customer::withCount(['vehicles', 'workOrders', 'washOrders'])
            ->withSum('invoices', 'total')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.customers.customers-component', compact('customers'))
            ->layout('components.layouts.app', ['title' => 'Customers']);
    }
}
