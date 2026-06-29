<?php

namespace App\Domains\Finance\Livewire\Quotations;

use App\Domains\Finance\Models\Quotation;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationsComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $search = trim((string) $this->search);

        $quotations = Quotation::query()
            ->with(['customer', 'workOrder', 'createdBy', 'approvedByUser', 'parentQuotation'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('quotation_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn($customer) => $customer->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('workOrder', fn($workOrder) => $workOrder->where('order_number', 'like', "%{$search}%"));
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when(session('current_branch_id'), fn($query) => $query->where('branch_id', session('current_branch_id')))
            ->latest()
            ->paginate((int) $this->perPage);

        return view('livewire.quotations.quotations-component', compact('quotations'))
            ->layout('components.layouts.app', ['title' => 'Quotations']);
    }
}
