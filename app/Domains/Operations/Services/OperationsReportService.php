<?php

namespace App\Domains\Operations\Services;

use App\Models\User;
use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OperationsReportService
{
    public function getWorkOrderStats(User $user, array $filters): array
    {
        $query = $this->buildWorkOrderQuery($user, $filters);

        return [
            'total' => (int) (clone $query)->count(),
            'completed' => (int) (clone $query)->where('status', 'delivered')->count(),
            'in_progress' => (int) (clone $query)->where('status', 'in_progress')->count(),
            'by_type' => (clone $query)->selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type'),
        ];
    }

    public function getWashOrderStats(User $user, array $filters): array
    {
        $query = $this->buildWashOrderQuery($user, $filters);

        return [
            'total' => (int) (clone $query)->count(),
            'completed' => (int) (clone $query)->where('status', 'completed')->count(),
            'combo' => (int) (clone $query)->where('source', 'combo')->count(),
            'walk_in' => (int) (clone $query)->where('source', 'walk_in')->count(),
        ];
    }

    public function getDailyVolume(User $user, array $filters): Collection
    {
        $workOrders = $this->buildWorkOrderQuery($user, $filters)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as work_orders')
            ->groupBy('date')
            ->pluck('work_orders', 'date');

        $washOrders = $this->buildWashOrderQuery($user, $filters)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as wash_orders')
            ->groupBy('date')
            ->pluck('wash_orders', 'date');

        return collect(array_unique(array_merge($workOrders->keys()->all(), $washOrders->keys()->all())))
            ->sort()
            ->values()
            ->map(function (string $date) use ($workOrders, $washOrders) {
                return [
                    'date' => $date,
                    'work_orders' => (int) ($workOrders[$date] ?? 0),
                    'wash_orders' => (int) ($washOrders[$date] ?? 0),
                ];
            });
    }

    private function buildWorkOrderQuery(User $user, array $filters): Builder
    {
        $query = WorkOrder::query()
            ->whereIn('branch_id', $this->resolveBranchIds($user, $filters['branch_id'] ?? null))
            ->whereBetween('created_at', [$filters['date_from'], $filters['date_to'] . ' 23:59:59']);

        if (!empty($filters['staff_id'])) {
            $query->where('assigned_technician_id', $filters['staff_id']);
        }

        return $query;
    }

    private function buildWashOrderQuery(User $user, array $filters): Builder
    {
        $query = WashOrder::query()
            ->whereIn('branch_id', $this->resolveBranchIds($user, $filters['branch_id'] ?? null))
            ->whereBetween('created_at', [$filters['date_from'], $filters['date_to'] . ' 23:59:59']);

        if (!empty($filters['staff_id'])) {
            $query->where('assigned_attendant_id', $filters['staff_id']);
        }

        return $query;
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
