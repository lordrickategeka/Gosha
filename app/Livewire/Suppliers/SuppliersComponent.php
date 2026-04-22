<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SuppliersComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $showCreateModal = false;
    public $name = '';
    public $contact_person = '';
    public $phone = '';
    public $email = '';
    public $address = '';

    protected $queryString = ['search' => ['except' => '']];

    public function createSupplier()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
        ]);

        Supplier::create([
            'name' => $this->name,
            'contact_person' => $this->contact_person,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => true,
        ]);

        $this->reset(['name', 'contact_person', 'phone', 'email', 'address', 'showCreateModal']);
        session()->flash('success', 'Supplier created successfully.');
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        session()->flash('success', $supplier->is_active ? 'Supplier activated.' : 'Supplier deactivated.');
    }

    public function render()
    {
        $suppliers = Supplier::withCount('inventoryMovements')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.suppliers.suppliers-component', compact('suppliers'))
            ->layout('components.layouts.app', ['title' => 'Suppliers']);
    }
}
