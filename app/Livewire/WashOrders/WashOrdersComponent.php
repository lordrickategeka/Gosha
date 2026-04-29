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

    // Assign-bay modal state
    public $showAssignBayModal = false;
    public $assigningOrderId = null;
    public $selectedBayId = '';
    public $selectedAttendantId = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'view'   => ['except' => 'queue'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Open the assign-bay chooser for a queued order
    public function openAssignBayModal(WashOrder $washOrder)
    {
        $this->authorize('change wash order status');
        $this->assigningOrderId   = $washOrder->id;
        $this->selectedBayId      = '';
        $this->selectedAttendantId = '';
        $this->showAssignBayModal = true;
    }

    public function closeAssignBayModal()
    {
        $this->showAssignBayModal  = false;
        $this->assigningOrderId    = null;
        $this->selectedBayId       = '';
        $this->selectedAttendantId = '';
    }

    // Confirm the bay selection and start the wash
    public function confirmAssignAndStart()
    {
        $this->authorize('change wash order status');

        $this->validate([
            'selectedBayId' => 'required|exists:wash_bays,id',
        ]);

        $washOrder = WashOrder::findOrFail($this->assigningOrderId);

        if (! $washOrder->canStart()) {
            session()->flash('error', 'This order cannot be started at this time.');
            $this->closeAssignBayModal();
            return;
        }

        $bay = WashBay::findOrFail($this->selectedBayId);

        if (! $bay->isAvailable()) {
            $this->addError('selectedBayId', 'Selected bay is no longer available.');
            return;
        }

        if ($this->selectedAttendantId) {
            $washOrder->update(['assigned_attendant_id' => $this->selectedAttendantId]);
        }

        $washOrder->start($bay);
        session()->flash('success', 'Wash started on ' . $bay->name . '.');
        $this->closeAssignBayModal();
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

    public function getAvailableBaysProperty()
    {
        return WashBay::where('branch_id', session('current_branch_id'))
            ->where('status', 'available')
            ->get();
    }

    public function getAttendantsProperty()
    {
        return User::where('vendor_id', auth()->user()->vendor_id)
            ->role('wash-attendant')
            ->orderBy('name')
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

    // Stats for the stats bar
    public function getStatsTodayQueuedProperty(): int
    {
        return WashOrder::where('branch_id', session('current_branch_id'))
            ->whereDate('created_at', today())
            ->where('status', 'queued')
            ->count();
    }

    public function getStatsInProgressProperty(): int
    {
        return WashOrder::where('branch_id', session('current_branch_id'))
            ->where('status', 'in_progress')
            ->count();
    }

    public function getStatsCompletedTodayProperty(): int
    {
        return WashOrder::where('branch_id', session('current_branch_id'))
            ->whereDate('completed_at', today())
            ->where('status', 'completed')
            ->count();
    }

    public function getStatsAvailableBaysProperty(): int
    {
        return WashBay::where('branch_id', session('current_branch_id'))
            ->where('status', 'available')
            ->count();
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
