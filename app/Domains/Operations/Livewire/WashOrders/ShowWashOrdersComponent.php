<?php

namespace App\Domains\Operations\Livewire\WashOrders;

use App\Domains\Operations\Models\WashBay;
use App\Domains\Operations\Models\WashOrder;
use Livewire\Component;

class ShowWashOrdersComponent extends Component
{
    public WashOrder $washOrder;

    public function mount(WashOrder $washOrder)
    {
        $this->washOrder = $washOrder->load(['vehicle', 'customer', 'washBay', 'assignedAttendant', 'items', 'invoice', 'workOrder']);
    }

    public function start($bayId = null)
    {
        $this->authorize('change_wash_order_status');
        $bay = $bayId ? WashBay::find($bayId) : null;
        $this->washOrder->start($bay);
        $this->washOrder->refresh();
        session()->flash('success', 'Wash started.');
    }

    public function complete()
    {
        $this->authorize('change_wash_order_status');
        $this->washOrder->complete();
        $this->washOrder->refresh();
        session()->flash('success', 'Wash completed.');
    }

    public function cancel()
    {
        $this->authorize('change_wash_order_status');
        $this->washOrder->cancel();
        $this->washOrder->refresh();
        session()->flash('success', 'Wash order cancelled.');
    }

    public function reverseToQueue()
    {
        $this->authorize('change_wash_order_status');

        if (! $this->washOrder->canReverse()) {
            session()->flash('error', 'Only an order currently being washed can be sent back to the queue.');
            return;
        }

        $this->washOrder->reverseToQueue();
        $this->washOrder->refresh();
        session()->flash('success', 'Order unassigned and moved back to the waiting queue.');
    }

    public function getAvailableBaysProperty()
    {
        return WashBay::where('branch_id', $this->washOrder->branch_id)->where('status', 'available')->get();
    }

    public function render()
    {
        return view('livewire.wash-orders.show-wash-orders-component')
            ->layout('components.layouts.app', ['title' => 'Wash Order ' . $this->washOrder->order_number]);
    }
}
