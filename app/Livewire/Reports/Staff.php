<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Models\User;
use App\Services\Reports\StaffReportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Staff Performance Report')]
class Staff extends Component
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

    public function getTechnicianStatsProperty()
    {
        return app(StaffReportService::class)->getTechnicianStats($this->currentUser(), $this->filters());
    }

    public function getWashAttendantStatsProperty()
    {
        return app(StaffReportService::class)->getWashAttendantStats($this->currentUser(), $this->filters());
    }

    public function getSummaryProperty()
    {
        return app(StaffReportService::class)->getSummary($this->currentUser(), $this->filters());
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
        return view('livewire.reports.staff');
    }
}
