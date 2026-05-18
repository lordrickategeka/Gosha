<?php

namespace App\Services\Finance;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Support\Collection;

class CreditorsAgingService
{
    public function getRows(User $user, array $filters = []): Collection
    {
        $asOfDate = isset($filters['as_of']) ? now()->parse($filters['as_of'])->startOfDay() : now()->startOfDay();
        $accessibleBranchIds = $user->getAccessibleBranchIds();

        $bills = Bill::query()
            ->with(['supplier:id,name,phone,email', 'branch:id,name'])
            ->whereIn('branch_id', $accessibleBranchIds)
            ->whereIn('status', ['received', 'partially_paid', 'overdue'])
            ->where('balance_due', '>', 0)
            ->when(! empty($filters['branch_id']), fn ($query) => $query->where('branch_id', $filters['branch_id']))
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->whereHas('supplier', function ($supplierQuery) use ($filters) {
                    $supplierQuery->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('phone', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->get();

        return $bills
            ->groupBy('supplier_id')
            ->map(function (Collection $supplierBills) use ($asOfDate) {
                $supplier = $supplierBills->first()->supplier;

                $buckets = [
                    'current' => 0.0,
                    'days_1_30' => 0.0,
                    'days_31_60' => 0.0,
                    'days_61_90' => 0.0,
                    'days_90_plus' => 0.0,
                ];

                foreach ($supplierBills as $bill) {
                    $days = $bill->due_date ? $bill->due_date->diffInDays($asOfDate, false) : 0;
                    $amount = (float) $bill->balance_due;

                    if ($days <= 0) {
                        $buckets['current'] += $amount;
                    } elseif ($days <= 30) {
                        $buckets['days_1_30'] += $amount;
                    } elseif ($days <= 60) {
                        $buckets['days_31_60'] += $amount;
                    } elseif ($days <= 90) {
                        $buckets['days_61_90'] += $amount;
                    } else {
                        $buckets['days_90_plus'] += $amount;
                    }
                }

                return [
                    'supplier_id' => $supplier?->id,
                    'supplier_name' => $supplier?->name ?? 'Unknown Supplier',
                    'phone' => $supplier?->phone,
                    'email' => $supplier?->email,
                    'bill_count' => $supplierBills->count(),
                    'total_due' => array_sum($buckets),
                    'buckets' => $buckets,
                ];
            })
            ->values()
            ->sortByDesc('total_due')
            ->values();
    }

    public function getTotals(Collection $rows): array
    {
        return [
            'suppliers' => $rows->count(),
            'bill_count' => (int) $rows->sum('bill_count'),
            'total_due' => (float) $rows->sum('total_due'),
            'current' => (float) $rows->sum(fn ($row) => $row['buckets']['current']),
            'days_1_30' => (float) $rows->sum(fn ($row) => $row['buckets']['days_1_30']),
            'days_31_60' => (float) $rows->sum(fn ($row) => $row['buckets']['days_31_60']),
            'days_61_90' => (float) $rows->sum(fn ($row) => $row['buckets']['days_61_90']),
            'days_90_plus' => (float) $rows->sum(fn ($row) => $row['buckets']['days_90_plus']),
        ];
    }
}
