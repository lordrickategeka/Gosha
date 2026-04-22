<?php

namespace App\Livewire\WashOrders;

use App\Models\User;
use App\Models\WashBay;
use App\Models\WashOrder;
use Livewire\Component;
use Livewire\WithPagination;

class WashOrdersComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $source = '';
    public $view = 'queue'; // queue, list

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'view' => ['except' => 'queue'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function startWash(WashOrder $washOrder, $bayId = null)
    {
        $this->authorize('change wash order status');

        if ($washOrder->canStart()) {
            $bay = $bayId ? WashBay::find($bayId) : null;
            $washOrder->start($bay);
            session()->flash('success', 'Wash started.');
        }
    }

    public function completeWash(WashOrder $washOrder)
    {
        $this->authorize('change wash order status');

        if ($washOrder->canComplete()) {
            $washOrder->complete();
            session()->flash('success', 'Wash completed.');
        }
    }

    public function prioritize(WashOrder $washOrder)
    {
        $this->authorize('manage wash queue');
        $washOrder->prioritize();
        session()->flash('success', 'Order prioritized.');
    }

    public function cancel(WashOrder $washOrder)
    {
        $this->authorize('change wash order status');
        $washOrder->cancel();
        session()->flash('success', 'Order cancelled.');
    }

    public function getWashBaysProperty()
    {
        return WashBay::where('branch_id', session('current_branch_id'))
            ->with('currentWashOrder.vehicle')
            ->get();
    }

    public function getQueueProperty()
    {
        return WashOrder::with(['vehicle', 'customer'])
            ->where('branch_id', session('current_branch_id'))
            ->where('status', 'queued')
            ->orderByQueue()
            ->get();
    }

    public function getInProgressProperty()
    {
        return WashOrder::with(['vehicle', 'customer', 'washBay', 'assignedAttendant'])
            ->where('branch_id', session('current_branch_id'))
            ->where('status', 'in_progress')
            ->get();
    }

    public function render()
    {
        $washOrders = WashOrder::with(['vehicle', 'customer', 'washBay', 'assignedAttendant'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', "%{$this->search}%")
                      ->orWhereHas('vehicle', fn($v) => $v->where('registration_number', 'like', "%{$this->search}%"))
                      ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->source, fn($q) => $q->where('source', $this->source))
            ->when(session('current_branch_id'), fn($q) => $q->where('branch_id', session('current_branch_id')))
            ->latest()
            ->paginate(15);

        return view('livewire.wash-orders.wash-orders-component', [
            'washOrders' => $washOrders,
        ])->layout('components.layouts.app', ['title' => 'Wash Bay']);
    }
}
