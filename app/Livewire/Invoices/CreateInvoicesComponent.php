<?php

namespace App\Livewire\Invoices;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\WashOrder;
use App\Models\WorkOrder;
use Livewire\Component;

class CreateInvoicesComponent extends Component
{
    public $customer_id = '';
    public $work_order_id = '';
    public $wash_order_id = '';
    public $due_date;
    public $notes = '';
    public $items = [];
    public $tax_rate = 18;
    public $discount = 0;

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'due_date' => 'required|date',
        'items' => 'required|array|min:1',
        'items.*.description' => 'required|string',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit_price' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->due_date = now()->addDays(7)->format('Y-m-d');

        if (request('work_order')) {
            $workOrder = WorkOrder::with(['customer', 'items'])->find(request('work_order'));
            if ($workOrder) {
                $this->work_order_id = $workOrder->id;
                $this->customer_id = $workOrder->customer_id;
                foreach ($workOrder->items as $item) {
                    $this->items[] = [
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                    ];
                }
            }
        }

        if (request('wash_order')) {
            $washOrder = WashOrder::with(['customer', 'items'])->find(request('wash_order'));
            if ($washOrder) {
                $this->wash_order_id = $washOrder->id;
                $this->customer_id = $washOrder->customer_id;
                foreach ($washOrder->items as $item) {
                    $this->items[] = [
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                    ];
                }
            }
        }
    }

    public function addItem()
    {
        $this->items[] = ['description' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getCustomersProperty()
    {
        return Customer::orderBy('name')->get();
    }

    public function getSubtotalProperty()
    {
        return collect($this->items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));
    }

    public function getTaxProperty()
    {
        return $this->subtotal * ($this->tax_rate / 100);
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->tax - $this->discount;
    }

    public function save()
    {
        $this->validate();

        $invoice = Invoice::create([
            'branch_id' => session('current_branch_id'),
            'customer_id' => $this->customer_id,
            'work_order_id' => $this->work_order_id ?: null,
            'wash_order_id' => $this->wash_order_id ?: null,
            'created_by' => auth()->id(),
            'subtotal' => $this->subtotal,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
            'balance_due' => $this->total,
            'due_date' => $this->due_date,
            'status' => 'pending',
            'notes' => $this->notes,
        ]);

        foreach ($this->items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        session()->flash('success', 'Invoice created successfully.');
        return redirect()->route('invoices.show', $invoice);
    }

    public function render()
    {
        return view('livewire.invoices.create-invoices-component')
            ->layout('components.layouts.app', ['title' => 'Create Invoice']);
    }
}
