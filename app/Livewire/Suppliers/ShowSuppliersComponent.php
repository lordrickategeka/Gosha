<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;

class ShowSuppliersComponent extends Component
{
    public Supplier $supplier;

    public function mount(Supplier $supplier)
    {
        $this->supplier = $supplier->load(['inventoryItems' => fn($q) => $q->take(20)]);
    }

    public function render()
    {
        return view('livewire.suppliers.show-suppliers-component');
    }
}
