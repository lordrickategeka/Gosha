<?php

namespace App\Domains\HR\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class StaffReportService
{
    public function getTechnicianStats(User $user, array $filters): Collection
    {
        $branchId = $filters['branch_id'] ?? null;
        $staffId = $filters['staff_id'] ?? null;

        return User::role('technician')
            ->where('vendor_id', $user->vendor_id)
            ->when($staffId, fn ($query) => $query->where('id', $staffId))
            ->withCount([
                'workOrdersAssigned as completed_orders' => function ($query) use ($filters, $branchId) {
                    $query->where('status', 'delivered')
                        ->whereBetween('delivered_at', [$filters['date_from'], $filters['date_to'] . ' 23:59:59'])
                        ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
                },
            ])
            ->withSum([
                'commissions as total_commission' => fn ($query) => $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to'] . ' 23:59:59']),
            ], 'commission_amount')
            ->orderByDesc('completed_orders')
            ->get();
    }

    public function getWashAttendantStats(User $user, array $filters): Collection
    {
        $branchId = $filters['branch_id'] ?? null;
        $staffId = $filters['staff_id'] ?? null;

        return User::role('wash-attendant')
            ->where('vendor_id', $user->vendor_id)
            ->when($staffId, fn ($query) => $query->where('id', $staffId))
            ->withCount([
                'washOrdersAssigned as completed_washes' => function ($query) use ($filters, $branchId) {
                    $query->where('status', 'completed')
                        ->whereBetween('completed_at', [$filters['date_from'], $filters['date_to'] . ' 23:59:59'])
                        ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
                },
            ])
            ->withSum([
                'commissions as total_commission' => fn ($query) => $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to'] . ' 23:59:59']),
            ], 'commission_amount')
            ->orderByDesc('completed_washes')
            ->get();
    }

    public function getSummary(User $user, array $filters): array
    {
        $tech = $this->getTechnicianStats($user, $filters);
        $wash = $this->getWashAttendantStats($user, $filters);

        return [
            'technicians' => $tech->count(),
            'attendants' => $wash->count(),
            'completed_orders' => (int) $tech->sum('completed_orders'),
            'completed_washes' => (int) $wash->sum('completed_washes'),
            'commission_total' => (float) ($tech->sum('total_commission') + $wash->sum('total_commission')),
        ];
    }
}
