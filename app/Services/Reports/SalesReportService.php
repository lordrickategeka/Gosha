<?php

namespace App\Services\Reports;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SalesReportService
{
    public function getStats(User $user, array $filters): array
    {
        $invoices = $this->buildInvoiceQuery($user, $filters);
        $payments = $this->buildPaymentQuery($user, $filters);

        return [
            'total_invoiced' => (float) (clone $invoices)->sum('total'),
            'total_collected' => (float) (clone $payments)->sum('amount'),
            'pending' => (float) (clone $invoices)->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance_due'),
            'invoice_count' => (int) (clone $invoices)->count(),
            'avg_invoice' => (float) ((clone $invoices)->avg('total') ?? 0),
        ];
    }

    public function getDailyRevenue(User $user, array $filters): Collection
    {
        return $this->buildPaymentQuery($user, $filters)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getPaymentMethods(User $user, array $filters): Collection
    {
        return $this->buildPaymentQuery($user, $filters)
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();
    }

    public function getDetailedInvoices(User $user, array $filters): Collection
    {
        return $this->buildInvoiceQuery($user, $filters)
            ->with(['customer:id,name', 'branch:id,name'])
            ->select(['id', 'invoice_number', 'customer_id', 'branch_id', 'status', 'total', 'amount_paid', 'balance_due', 'issue_date', 'created_at'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getSummaryRows(User $user, array $filters): Collection
    {
        $stats = $this->getStats($user, $filters);

        return collect([
            ['Metric', 'Value'],
            ['Total Invoiced', $stats['total_invoiced']],
            ['Total Collected', $stats['total_collected']],
            ['Pending Balance', $stats['pending']],
            ['Invoice Count', $stats['invoice_count']],
            ['Average Invoice', $stats['avg_invoice']],
        ]);
    }

    private function buildInvoiceQuery(User $user, array $filters): Builder
    {
        $branchIds = $this->resolveBranchIds($user, $filters['branch_id'] ?? null);

        return Invoice::query()
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('created_at', [$filters['date_from'], $filters['date_to'] . ' 23:59:59']);
    }

    private function buildPaymentQuery(User $user, array $filters): Builder
    {
        $branchIds = $this->resolveBranchIds($user, $filters['branch_id'] ?? null);

        return Payment::query()
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$filters['date_from'], $filters['date_to'] . ' 23:59:59'])
            ->whereHas('invoice', function (Builder $query) use ($branchIds) {
                $query->whereIn('branch_id', $branchIds);
            });
    }

    private function resolveBranchIds(User $user, ?int $branchId): array
    {
        $accessible = $user->getAccessibleBranchIds();

        if ($branchId && in_array($branchId, $accessible, true)) {
            return [$branchId];
        }

        return $accessible;
    }
}
