<?php
namespace App\Domains\Organization\Livewire\Dashboard;

use App\Domains\Operations\Models\Appointment;
use App\Domains\CRM\Models\Customer;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Domains\Operations\Models\ServiceBay;
use App\Domains\Operations\Models\WashBay;
use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardComponent extends Component
{
    public $period = 'today';

    public function mount()
    {
        $this->period = request('period', 'today');
    }

    public function setPeriod($period)
    {
        $this->period = $period;
    }

    protected function branchId()
    {
        return session('current_branch_id');
    }

    protected function periodQuery($query)
    {
        return match ($this->period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            default => $query->whereDate('created_at', today()),
        };
    }

    protected function branchQuery($model)
    {
        $query = $model::query();
        if ($this->branchId()) {
            $query->where('branch_id', $this->branchId());
        }
        return $this->periodQuery($query);
    }

    public function getStatsProperty()
    {
        // Work Orders
        $workOrders = $this->branchQuery(WorkOrder::class)->count();
        $completedWorkOrders = $this->branchQuery(WorkOrder::class)->where('status', 'delivered')->count();

        // Wash Orders
        $washOrders = $this->branchQuery(WashOrder::class)->count();
        $completedWashOrders = $this->branchQuery(WashOrder::class)->where('status', 'completed')->count();

        // Revenue (Payment has no branch_id — filter through invoice)
        $revenueQuery = Payment::query()->where('status', 'completed');
        if ($this->branchId()) {
            $revenueQuery->whereHas('invoice', fn($q) => $q->where('branch_id', $this->branchId()));
        }
        $revenue = $this->periodQuery($revenueQuery)->sum('amount');

        // Expenses
        $expenses = $this->branchQuery(Expense::class)->where('status', 'approved')->sum('amount');

        return [
            'work_orders' => $workOrders,
            'completed_work_orders' => $completedWorkOrders,
            'wash_orders' => $washOrders,
            'completed_wash_orders' => $completedWashOrders,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $revenue - $expenses,
        ];
    }

    public function getServiceBaysProperty()
    {
        return ServiceBay::with('currentWorkOrder.vehicle')
            ->when($this->branchId(), fn($q) => $q->where('branch_id', $this->branchId()))
            ->get()
            ->groupBy('status');
    }

    public function getWashBaysProperty()
    {
        return WashBay::with('currentWashOrder.vehicle')
            ->when($this->branchId(), fn($q) => $q->where('branch_id', $this->branchId()))
            ->get()
            ->groupBy('status');
    }

    public function getActiveWorkOrdersProperty()
    {
        return WorkOrder::with(['vehicle', 'customer', 'assignedTechnician', 'serviceBay'])
            ->when($this->branchId(), fn($q) => $q->where('branch_id', $this->branchId()))
            ->active()
            ->latest()
            ->take(5)
            ->get();
    }

    public function getWashQueueProperty()
    {
        return WashOrder::with(['vehicle', 'customer', 'assignedAttendant'])
            ->when($this->branchId(), fn($q) => $q->where('branch_id', $this->branchId()))
            ->active()
            ->orderByQueue()
            ->take(5)
            ->get();
    }

    public function getTodayAppointmentsProperty()
    {
        return Appointment::with(['vehicle', 'customer'])
            ->when($this->branchId(), fn($q) => $q->where('branch_id', $this->branchId()))
            ->today()
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('scheduled_time')
            ->take(5)
            ->get();
    }

    public function getUnpaidInvoicesProperty()
    {
        return Invoice::with('customer')
            ->when($this->branchId(), fn($q) => $q->where('branch_id', $this->branchId()))
            ->unpaid()
            ->orderBy('due_date')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard-component')
            ->layout('components.layouts.app', ['title' => 'Dashboard']);
    }
}
