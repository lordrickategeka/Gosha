<?php

namespace App\Services\Reports;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InventoryReportService
{
    public function getOverview(User $user, array $filters): array
    {
        $items = InventoryItem::query()->where('vendor_id', $user->vendor_id);
        $movements = $this->buildMovementsQuery($user, $filters);

        return [
            'total_items' => (int) (clone $items)->count(),
            'in_stock' => (int) (clone $items)->where('quantity', '>', 0)->count(),
            'low_stock' => (int) (clone $items)->whereColumn('quantity', '<=', 'reorder_level')->count(),
            'out_of_stock' => (int) (clone $items)->where('quantity', '<=', 0)->count(),
            'stock_value' => (float) ((clone $items)->selectRaw('SUM(quantity * cost_price) as total')->value('total') ?? 0),
            'movement_count' => (int) (clone $movements)->count(),
        ];
    }

    public function getLowStockItems(User $user): Collection
    {
        return InventoryItem::query()
            ->where('vendor_id', $user->vendor_id)
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->with(['category:id,name', 'supplier:id,name'])
            ->orderBy('quantity')
            ->limit(20)
            ->get();
    }

    public function getMovementByType(User $user, array $filters): Collection
    {
        return $this->buildMovementsQuery($user, $filters)
            ->selectRaw('movement_type, COUNT(*) as count, SUM(quantity) as quantity_sum')
            ->groupBy('movement_type')
            ->orderByDesc('count')
            ->get();
    }

    public function getRecentMovements(User $user, array $filters): Collection
    {
        return $this->buildMovementsQuery($user, $filters)
            ->with(['inventoryItem:id,name,sku', 'branch:id,name', 'performedBy:id,name'])
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();
    }

    private function buildMovementsQuery(User $user, array $filters): Builder
    {
        $query = InventoryMovement::query()
            ->whereBetween('created_at', [$filters['date_from'], $filters['date_to'] . ' 23:59:59'])
            ->whereHas('inventoryItem', function (Builder $query) use ($user) {
                $query->where('vendor_id', $user->vendor_id);
            });

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['staff_id'])) {
            $query->where('performed_by', $filters['staff_id']);
        }

        return $query;
    }
}
