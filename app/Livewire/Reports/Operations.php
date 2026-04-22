<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Models\User;
use App\Services\Reports\OperationsReportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Operations Report')]
class Operations extends Component
{
    public $period = 'month';
    public $dateFrom;
    public $dateTo;
    public $branchId = '';
    public $staffId = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function setPeriod($period)
    {
        $this->period = $period;

        match ($period) {
            'today' => $this->dateFrom = $this->dateTo = now()->format('Y-m-d'),
            'week' => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek()->format('Y-m-d'), now()->format('Y-m-d')],
            'month' => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'year' => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
            default => null,
        };
    }

    public function getWorkOrderStatsProperty()
    {
        return app(OperationsReportService::class)->getWorkOrderStats($this->currentUser(), $this->filters());
    }

    public function getWashOrderStatsProperty()
    {
        return app(OperationsReportService::class)->getWashOrderStats($this->currentUser(), $this->filters());
    }

    public function getDailyVolumeProperty()
    {
        return app(OperationsReportService::class)->getDailyVolume($this->currentUser(), $this->filters());
    }

    public function getAvailableBranchesProperty()
    {
        return Branch::query()
            ->whereIn('id', $this->currentUser()->getAccessibleBranchIds())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getAvailableStaffProperty()
    {
        return User::query()
            ->where('vendor_id', $this->currentUser()->vendor_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function currentUser(): User
    {
        return Auth::user();
    }

    private function filters(): array
    {
        return [
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'branch_id' => $this->branchId !== '' ? (int) $this->branchId : null,
            'staff_id' => $this->staffId !== '' ? (int) $this->staffId : null,
        ];
    }

    public function render()
    {
        return view('livewire.reports.operations');
    }
}
