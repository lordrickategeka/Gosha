<?php

namespace App\Domains\Finance\Livewire\Payments;

use App\Domains\Finance\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentsComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $method = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = ['search' => ['except' => ''], 'method' => ['except' => '']];

    public function render()
    {
        $payments = Payment::with(['invoice.customer', 'receivedBy'])
            ->where('status', 'completed')
            ->when($this->search, fn($q) => $q->whereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%"))
                ->orWhere('reference_number', 'like', "%{$this->search}%"))
            ->when($this->method, fn($q) => $q->where('payment_method', $this->method))
            ->when($this->dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('payment_date', '<=', $this->dateTo))
            ->latest('payment_date')
            ->paginate(20);

        $totals = [
            'today' => Payment::where('status', 'completed')->whereDate('payment_date', today())->sum('amount'),
            'week' => Payment::where('status', 'completed')->whereBetween('payment_date', [now()->startOfWeek(), now()])->sum('amount'),
            'month' => Payment::where('status', 'completed')->whereMonth('payment_date', now()->month)->sum('amount'),
        ];

        return view('livewire.payments.payments-component', compact('payments', 'totals'))
            ->layout('components.layouts.app', ['title' => 'Payments']);
    }
}
