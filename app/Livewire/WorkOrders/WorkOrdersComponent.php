<?php

namespace App\Livewire\WorkOrders;

use App\Models\ServiceBay;
use App\Models\User;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\WithPagination;

class WorkOrdersComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $type = '';
    public $technician = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 6;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'type' => ['except' => ''],
        'technician' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status', 'type', 'technician', 'dateFrom', 'dateTo']);
    }

    public function startWorkOrder(WorkOrder $workOrder)
    {
        $this->authorize('change work order status');

        if ($workOrder->canStart()) {
            $workOrder->start();
            session()->flash('success', 'Work order started.');
        }
    }

    public function markReady(WorkOrder $workOrder)
    {
        $this->authorize('change work order status');

        if ($workOrder->canComplete()) {
            $workOrder->markReady();
            session()->flash('success', 'Work order marked as ready.');
        }
    }

    public function deliver(WorkOrder $workOrder)
    {
        $this->authorize('change work order status');

        if ($workOrder->canDeliver()) {
            $workOrder->deliver();
            session()->flash('success', 'Vehicle delivered to customer.');
        }
    }

    public function getTechniciansProperty()
    {
        return User::role('technician')->where('is_active', true)->get();
    }

    public function getServiceBaysProperty()
    {
        return ServiceBay::where('branch_id', session('current_branch_id'))->get();
    }

    public function render()
    {
        $workOrders = WorkOrder::with(['vehicle', 'customer', 'assignedTechnician', 'serviceBay'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', "%{$this->search}%")
                      ->orWhereHas('vehicle', fn($v) => $v->where('registration_number', 'like', "%{$this->search}%"))
                      ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->technician, fn($q) => $q->where('assigned_technician_id', $this->technician))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when(session('current_branch_id'), fn($q) => $q->where('branch_id', session('current_branch_id')))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.work-orders.work-orders-component', [
            'workOrders' => $workOrders,
        ])->layout('components.layouts.app', ['title' => 'Work Orders']);
    }
}
