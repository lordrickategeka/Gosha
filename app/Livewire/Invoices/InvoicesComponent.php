<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoicesComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = ['search' => ['except' => ''], 'status' => ['except' => '']];

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        $invoices = Invoice::with(['customer', 'workOrder', 'washOrder'])
            ->when($this->search, fn($q) => $q->where('invoice_number', 'like', "%{$this->search}%")->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%")))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when(session('current_branch_id'), fn($q) => $q->where('branch_id', session('current_branch_id')))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Invoice::sum('total'),
            'paid' => Invoice::where('status', 'paid')->sum('total'),
            'pending' => Invoice::whereIn('status', ['sent', 'partial'])->sum('balance_due'),
            'overdue' => Invoice::overdue()->sum('balance_due'),
        ];

        return view('livewire.invoices.invoices-component', compact('invoices', 'stats'))
            ->layout('components.layouts.app', ['title' => 'Invoices']);
    }
}
