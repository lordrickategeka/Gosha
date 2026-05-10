<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;

class ShowInvoicesComponent extends Component
{
    public Invoice $invoice;
    public $showPaymentModal = false;
    public $paymentAmount = 0;
    public $paymentMethod = 'cash';
    public $paymentReference = '';

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['customer', 'items', 'payments', 'workOrder', 'washOrder']);
        $this->paymentAmount = $invoice->balance_due;
    }

    public function recordPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1|max:' . $this->invoice->balance_due,
            'paymentMethod' => 'required|in:cash,mobile_money,card,bank_transfer,credit',
        ]);

        $payment = Payment::create([
            'branch_id' => $this->invoice->branch_id,
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->invoice->customer_id,
            'received_by' => auth()->id(),
            'amount' => $this->paymentAmount,
            'payment_method' => $this->paymentMethod,
            'reference_number' => $this->paymentReference,
            'status' => 'completed',
            'payment_date' => today(),
        ]);

        $this->invoice->refresh();
        $this->invoice->recalculateTotals();
        $this->invoice->saveQuietly();
        $this->invoice->refresh();

        $this->showPaymentModal = false;
        $this->paymentAmount = $this->invoice->balance_due;
        $this->paymentReference = '';

        session()->flash('success', 'Payment recorded successfully.');
    }

    public function render()
    {
        return view('livewire.invoices.show-invoices-component')
            ->layout('components.layouts.app', ['title' => 'Invoice ' . $this->invoice->invoice_number]);
    }
}
