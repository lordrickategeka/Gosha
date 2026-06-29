<?php

namespace App\Domains\Organization\Livewire\Reports;

use App\Domains\Organization\Models\Branch;
use App\Models\User;
use App\Domains\Finance\Services\DebtorsAgingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Debtors')]
class Debtors extends Component
{
    public $asOfDate;
    public $branchId = '';
    public $search = '';

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function getRowsProperty()
    {
        return app(DebtorsAgingService::class)->getRows($this->currentUser(), $this->filters());
    }

    public function getTotalsProperty(): array
    {
        return app(DebtorsAgingService::class)->getTotals($this->rows);
    }

    public function getAvailableBranchesProperty()
    {
        return Branch::query()
            ->whereIn('id', $this->currentUser()->getAccessibleBranchIds())
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
            'as_of' => $this->asOfDate,
            'branch_id' => $this->branchId !== '' ? (int) $this->branchId : null,
            'search' => trim($this->search),
        ];
    }

    public function render()
    {
        return view('livewire.reports.debtors');
    }
}
