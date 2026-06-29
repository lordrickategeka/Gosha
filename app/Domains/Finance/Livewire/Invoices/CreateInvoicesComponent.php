<?php

namespace App\Domains\Finance\Livewire\Invoices;

use App\Domains\Organization\Models\Branch;
use App\Domains\CRM\Models\Customer;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Models\WorkOrder;
use Livewire\Component;

class CreateInvoicesComponent extends Component
{
    public $customer_id = '';
    public $work_order_id = '';
    public $wash_order_id = '';
    public $showCustomerModal = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerEmail = '';
    public $due_date;
    public $notes = '';
    public $items = [];
    public $tax_rate = 18;
    public $discount = 0;

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'due_date' => 'required|date',
        'tax_rate' => 'nullable|numeric|min:0',
        'discount' => 'nullable|numeric|min:0',
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

    public function openCustomerModal()
    {
        $this->showCustomerModal = true;
        $this->resetCustomerModalFields();
    }

    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerModalFields();
    }

    public function saveNewCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerPhone' => 'required|string|max:20',
            'newCustomerEmail' => 'nullable|email|max:255',
        ]);

        $branchVendorId = Branch::where('id', session('current_branch_id'))->value('vendor_id');
        $vendorId = $branchVendorId ?: auth()->user()->vendor_id;

        if (! $vendorId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Unable to determine vendor for this customer. Please select a branch and try again.',
            ]);

            return;
        }

        $customer = Customer::create([
            'vendor_id' => $vendorId,
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
        ]);

        $this->customer_id = $customer->id;
        $this->closeCustomerModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Customer created successfully.',
        ]);
    }

    protected function resetCustomerModalFields()
    {
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->resetValidation(['newCustomerName', 'newCustomerPhone', 'newCustomerEmail']);
    }

    public function getCustomersProperty()
    {
        return Customer::where('vendor_id', auth()->user()->vendor_id)
            ->orderBy('name')->get();
    }

    public function getSubtotalProperty()
    {
        return collect($this->items)->sum(fn($item) => $this->toMoney($item['quantity'] ?? 0) * $this->toMoney($item['unit_price'] ?? 0));
    }

    public function getTaxProperty()
    {
        return $this->subtotal * ($this->toMoney($this->tax_rate) / 100);
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->tax - $this->toMoney($this->discount);
    }

    protected function toMoney($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return is_numeric($value) ? (float) $value : 0.0;
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
            'tax_rate' => $this->toMoney($this->tax_rate),
            'tax_amount' => $this->tax,
            'discount_amount' => $this->toMoney($this->discount),
            'total' => $this->total,
            'balance_due' => $this->total,
            'issue_date' => now()->toDateString(),
            'due_date' => $this->due_date,
            'status' => 'sent',
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
