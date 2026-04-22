<?php

namespace App\Livewire\Reports;

use App\Exports\SalesReportExport;
use App\Models\Branch;
use App\Models\User;
use App\Services\Reports\SalesReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Sales Report')]
class Sales extends Component
{
    public $period = 'month';
    public $dateFrom;
    public $dateTo;
    public $branchId = '';
    public $staffId = '';
    public $exportFormat = 'pdf';
    public $exportType = 'summary';

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

    public function exportReport()
    {
        $service = app(SalesReportService::class);
        $filters = $this->filters();
        $stats = $service->getStats($this->currentUser(), $filters);
        $detailedRows = $service->getDetailedInvoices($this->currentUser(), $filters);

        if ($this->exportFormat === 'excel') {
            $rows = $this->exportType === 'detailed'
                ? $this->buildDetailedExportRows($detailedRows)
                : $service->getSummaryRows($this->currentUser(), $filters);

            return Excel::download(new SalesReportExport($rows), 'sales-report-' . now()->format('YmdHis') . '.xlsx');
        }

        $pdf = Pdf::loadView('reports.exports.sales-pdf', [
            'stats' => $stats,
            'detailedRows' => $detailedRows,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'exportType' => $this->exportType,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'sales-report-' . now()->format('YmdHis') . '.pdf');
    }

    public function getStatsProperty()
    {
        return app(SalesReportService::class)->getStats($this->currentUser(), $this->filters());
    }

    public function getDailyRevenueProperty()
    {
        return app(SalesReportService::class)->getDailyRevenue($this->currentUser(), $this->filters());
    }

    public function getPaymentMethodsProperty()
    {
        return app(SalesReportService::class)->getPaymentMethods($this->currentUser(), $this->filters());
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

    private function buildDetailedExportRows($detailedRows)
    {
        $rows = collect([
            ['Invoice Number', 'Date', 'Customer', 'Branch', 'Status', 'Total', 'Paid', 'Balance'],
        ]);

        foreach ($detailedRows as $row) {
            $rows->push([
                $row->invoice_number,
                optional($row->issue_date)->format('Y-m-d') ?? optional($row->created_at)->format('Y-m-d'),
                $row->customer?->name ?? 'N/A',
                $row->branch?->name ?? 'N/A',
                $row->status,
                (float) $row->total,
                (float) $row->amount_paid,
                (float) $row->balance_due,
            ]);
        }

        return $rows;
    }

    public function render()
    {
        return view('livewire.reports.sales');
    }
}
