<?php

namespace App\Shared\Navigation;

use App\Domains\CRM\Models\Customer;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Domains\Inventory\Models\InventoryItem;
use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Models\WorkOrder;
use App\Domains\Organization\Models\Branch;
use App\Domains\ServiceConfig\Models\ServiceTemplate;
use App\Domains\ServiceConfig\Models\ServiceType;
use App\Domains\Vehicles\Models\Vehicle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

/**
 * Live stat rows + badge for each module launcher card. Every figure reuses
 * an existing model scope or the same branch/vendor-scoping idiom already
 * used by DashboardComponent / VehiclesComponent / UsersComponent — nothing
 * here is invented business logic.
 */
class ModuleStats
{
    public static function forModule(string $key): array
    {
        return match ($key) {
            'work' => static::workOrders(),
            'wash' => static::washBay(),
            'customers' => static::customers(),
            'finance' => static::finance(),
            'stock' => static::inventory(),
            'reports' => static::reports(),
            'hr' => static::hr(),
            'settings' => static::settings(),
            default => ['badge' => null, 'rows' => []],
        };
    }

    protected static function branchId(): mixed
    {
        return session('current_branch_id');
    }

    /**
     * WorkOrder/WashOrder/Invoice use BelongsToBranch (no auto vendor/branch
     * global scope) — scope manually by the session branch, same as
     * DashboardComponent::branchQuery().
     */
    protected static function branchScoped(string $model): Builder
    {
        return $model::query()->when(
            static::branchId(),
            fn (Builder $q) => $q->where('branch_id', static::branchId())
        );
    }

    protected static function money(float $amount): string
    {
        return 'UGX '.number_format($amount);
    }

    protected static function workOrders(): array
    {
        $open = static::branchScoped(WorkOrder::class)->open()->count();
        $promisedToday = static::branchScoped(WorkOrder::class)->active()
            ->whereDate('estimated_completion', today())->count();
        $awaitingApproval = static::branchScoped(WorkOrder::class)
            ->where('status', 'quoted')->count();
        $atRisk = static::branchScoped(WorkOrder::class)->active()
            ->whereNotNull('estimated_completion')
            ->where('estimated_completion', '<', now())
            ->count();

        return [
            'badge' => $atRisk > 0 ? ['label' => "{$atRisk} at risk", 'tone' => 'error'] : null,
            'rows' => [
                ['label' => 'Open', 'value' => $open],
                ['label' => 'Promised today', 'value' => $promisedToday],
                ['label' => 'Awaiting approval', 'value' => $awaitingApproval],
            ],
        ];
    }

    protected static function washBay(): array
    {
        $queued = static::branchScoped(WashOrder::class)->queued()->count();
        $inProgress = static::branchScoped(WashOrder::class)->inProgress()->count();
        $completedToday = static::branchScoped(WashOrder::class)
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->count();

        return [
            'badge' => $queued > 0 ? ['label' => "{$queued} waiting", 'tone' => 'warning'] : null,
            'rows' => [
                ['label' => 'In queue', 'value' => $queued],
                ['label' => 'Washing', 'value' => $inProgress],
                ['label' => 'Ready today', 'value' => $completedToday],
            ],
        ];
    }

    protected static function customers(): array
    {
        $customers = Customer::count();
        $vehicles = Vehicle::whereHas(
            'customer',
            fn ($q) => $q->where('vendor_id', auth()->user()?->vendor_id)
        )->count();
        $withBalance = Customer::whereHas(
            'invoices',
            fn ($q) => $q->whereIn('status', ['sent', 'partial', 'overdue'])->where('balance_due', '>', 0)
        )->count();

        return [
            'badge' => null,
            'rows' => [
                ['label' => 'Customers', 'value' => $customers],
                ['label' => 'Vehicles', 'value' => $vehicles],
                ['label' => 'With balance', 'value' => $withBalance],
            ],
        ];
    }

    protected static function finance(): array
    {
        $branchId = static::branchId();

        $collectedTodayQuery = Payment::query()->where('status', 'completed')->whereDate('created_at', today());
        if ($branchId) {
            $collectedTodayQuery->whereHas('invoice', fn ($q) => $q->where('branch_id', $branchId));
        }
        $collectedToday = $collectedTodayQuery->sum('amount');

        $unpaid = static::branchScoped(Invoice::class)->unpaid()->count();
        $overdue = static::branchScoped(Invoice::class)->overdue()->count();

        return [
            'badge' => $overdue > 0 ? ['label' => "{$overdue} overdue", 'tone' => 'error'] : null,
            'rows' => [
                ['label' => 'Collected today', 'value' => static::money($collectedToday)],
                ['label' => 'Unpaid', 'value' => $unpaid],
                ['label' => 'Overdue', 'value' => $overdue],
            ],
        ];
    }

    protected static function inventory(): array
    {
        $skus = InventoryItem::count();
        $lowStock = InventoryItem::lowStock()->count();
        $stockValue = InventoryItem::query()->selectRaw('SUM(quantity * cost_price) as total')->value('total') ?? 0;

        return [
            'badge' => $lowStock > 0 ? ['label' => "{$lowStock} reorder", 'tone' => 'error'] : null,
            'rows' => [
                ['label' => 'SKUs', 'value' => $skus],
                ['label' => 'Below reorder', 'value' => $lowStock],
                ['label' => 'Stock value', 'value' => static::money((float) $stockValue)],
            ],
        ];
    }

    protected static function reports(): array
    {
        $branchId = static::branchId();

        $revenueQuery = Payment::query()->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        if ($branchId) {
            $revenueQuery->whereHas('invoice', fn ($q) => $q->where('branch_id', $branchId));
        }
        $revenueThisMonth = $revenueQuery->sum('amount');

        $workOrdersThisMonth = static::branchScoped(WorkOrder::class)->thisMonth()->count();
        $branches = Branch::count();

        return [
            'badge' => null,
            'rows' => [
                ['label' => 'Revenue this month', 'value' => static::money($revenueThisMonth)],
                ['label' => 'Work orders this month', 'value' => $workOrdersThisMonth],
                ['label' => 'Branches', 'value' => $branches],
            ],
        ];
    }

    protected static function hr(): array
    {
        $staff = User::where('vendor_id', auth()->user()?->vendor_id)->active()->count();
        $roles = Role::count();

        return [
            'badge' => null,
            'rows' => [
                ['label' => 'Staff', 'value' => $staff],
                ['label' => 'Roles', 'value' => $roles],
            ],
        ];
    }

    protected static function settings(): array
    {
        return [
            'badge' => null,
            'rows' => [
                ['label' => 'Branches', 'value' => Branch::count()],
                ['label' => 'Service types', 'value' => ServiceType::count()],
                ['label' => 'Templates', 'value' => ServiceTemplate::count()],
            ],
        ];
    }
}
