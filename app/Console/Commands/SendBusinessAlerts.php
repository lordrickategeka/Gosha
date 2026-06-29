<?php

namespace App\Console\Commands;

use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Notifications\PendingExpenseApprovalAlert;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Notifications\OverdueInvoiceAlert;
use App\Domains\Inventory\Models\InventoryItem;
use App\Domains\Inventory\Notifications\ExpiringStockAlert;
use App\Domains\Inventory\Notifications\LowStockAlert;
use App\Domains\Inventory\Notifications\OutOfStockAlert;
use App\Domains\Operations\Models\Appointment;
use App\Domains\Operations\Models\WorkOrder;
use App\Domains\Operations\Notifications\StaleWorkOrderAlert;
use App\Domains\Operations\Notifications\TodayAppointmentAlert;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendBusinessAlerts extends Command
{
    protected $signature = 'alerts:send {--dry-run : Log without sending notifications}';
    protected $description = 'Send daily business alerts across all modules to relevant branch users';

    private bool $dryRun = false;
    private int $sent = 0;
    private int $skipped = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY RUN — no notifications will be sent.');
        }

        $this->checkInventory();
        $this->checkOverdueInvoices();
        $this->checkStaleWorkOrders();
        $this->checkTodayAppointments();
        $this->checkPendingExpenseApprovals();

        $this->info("Done. Sent: {$this->sent}, Skipped (already notified today): {$this->skipped}");

        return self::SUCCESS;
    }

    private function checkInventory(): void
    {
        $this->line('→ Inventory');

        InventoryItem::active()->outOfStock()->with('branch')->each(function (InventoryItem $item) {
            $this->notifyBranchUsers(
                $item->branch_id,
                ['branch_manager', 'storekeeper'],
                'out-of-stock-alert',
                'inventory_item_id',
                $item->id,
                fn(User $user) => new OutOfStockAlert($item)
            );
        });

        InventoryItem::active()->lowStock()->where('quantity', '>', 0)->with('branch')->each(function (InventoryItem $item) {
            $this->notifyBranchUsers(
                $item->branch_id,
                ['branch_manager', 'storekeeper'],
                'low-stock-alert',
                'inventory_item_id',
                $item->id,
                fn(User $user) => new LowStockAlert($item)
            );
        });

        InventoryItem::active()->expiringSoon(30)->with('branch')->each(function (InventoryItem $item) {
            $this->notifyBranchUsers(
                $item->branch_id,
                ['branch_manager', 'storekeeper'],
                'expiring-stock-alert',
                'inventory_item_id',
                $item->id,
                fn(User $user) => new ExpiringStockAlert($item)
            );
        });
    }

    private function checkOverdueInvoices(): void
    {
        $this->line('→ Overdue invoices');

        Invoice::overdue()->with(['customer', 'branch'])->each(function (Invoice $invoice) {
            $this->notifyBranchUsers(
                $invoice->branch_id,
                ['branch_manager', 'accountant'],
                'overdue-invoice-alert',
                'invoice_id',
                $invoice->id,
                fn(User $user) => new OverdueInvoiceAlert($invoice)
            );
        });
    }

    private function checkStaleWorkOrders(): void
    {
        $this->line('→ Stale work orders (open > 3 days)');

        WorkOrder::whereIn('status', ['open', 'in_progress'])
            ->where('created_at', '<=', now()->subDays(3))
            ->with(['customer', 'branch'])
            ->each(function (WorkOrder $workOrder) {
                $this->notifyBranchUsers(
                    $workOrder->branch_id,
                    ['branch_manager', 'service_advisor'],
                    'stale-work-order-alert',
                    'work_order_id',
                    $workOrder->id,
                    fn(User $user) => new StaleWorkOrderAlert($workOrder)
                );
            });
    }

    private function checkTodayAppointments(): void
    {
        $this->line('→ Today\'s appointments');

        Appointment::today()
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->with(['customer', 'vehicle', 'branch'])
            ->each(function (Appointment $appointment) {
                $users = $this->getUsersForBranch(
                    $appointment->branch_id,
                    ['branch_manager', 'service_advisor', 'receptionist']
                );

                // Also notify the assigned person directly if they are active
                if ($appointment->assigned_to) {
                    $assigned = User::where('id', $appointment->assigned_to)->where('is_active', true)->first();
                    if ($assigned) {
                        $users = $users->push($assigned)->unique('id');
                    }
                }

                foreach ($users as $user) {
                    $this->sendOrSkip(
                        $user,
                        'today-appointment-alert',
                        'appointment_id',
                        $appointment->id,
                        new TodayAppointmentAlert($appointment)
                    );
                }
            });
    }

    private function checkPendingExpenseApprovals(): void
    {
        $this->line('→ Pending expense approvals');

        Expense::pendingApproval()->with(['recordedBy', 'branch'])->each(function (Expense $expense) {
            $this->notifyBranchUsers(
                $expense->branch_id,
                ['branch_manager', 'accountant'],
                'pending-expense-approval-alert',
                'expense_id',
                $expense->id,
                fn(User $user) => new PendingExpenseApprovalAlert($expense)
            );
        });
    }

    private function notifyBranchUsers(
        ?int $branchId,
        array $roles,
        string $type,
        string $entityKey,
        mixed $entityId,
        \Closure $makeNotification
    ): void {
        if (! $branchId) {
            return;
        }

        foreach ($this->getUsersForBranch($branchId, $roles) as $user) {
            $this->sendOrSkip($user, $type, $entityKey, $entityId, $makeNotification($user));
        }
    }

    private function sendOrSkip(User $user, string $type, string $entityKey, mixed $entityId, object $notification): void
    {
        if ($this->alreadySentToday($user, $type, $entityKey, $entityId)) {
            $this->skipped++;
            return;
        }

        if ($this->dryRun) {
            $this->line("  [DRY] user #{$user->id} ({$user->name}) ← {$type} #{$entityId}");
            $this->sent++;
            return;
        }

        $user->notify($notification);
        $this->sent++;
    }

    private function getUsersForBranch(int $branchId, array $roles): \Illuminate\Support\Collection
    {
        return User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->whereIn('name', $roles))
            ->get();
    }

    private function alreadySentToday(User $user, string $type, string $entityKey, mixed $entityId): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('type', $type)
            ->whereDate('created_at', today())
            ->whereRaw("JSON_EXTRACT(data, '$.$entityKey') = ?", [$entityId])
            ->exists();
    }
}
