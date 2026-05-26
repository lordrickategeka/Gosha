<?php

namespace App\Livewire\Reports;

use App\Models\Bill;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Finance\CreditorsAgingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Creditors')]
class Creditors extends Component
{
    public $asOfDate;
    public $branchId = '';
    public $search = '';

    public $newSupplierId = '';
    public $newBillDate = '';
    public $newDueDate = '';
    public $newBillTotal = '';
    public $newDescription = '';

    public $newStaffUserId = '';
    public $newStaffAmount = '';
    public $newStaffDescription = '';

    public $paymentBillId = null;
    public $paymentAmount = '';
    public $paymentMethod = 'cash';
    public $paymentDate = '';
    public $paymentReference = '';

    public $paymentCommissionId = null;
    public $staffPaymentAmount = '';
    public $staffPaymentReference = '';

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
        $this->newBillDate = now()->format('Y-m-d');
        $this->newDueDate = now()->addDays(30)->format('Y-m-d');
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function createBill(): void
    {
        abort_unless(Gate::allows('manage_creditors'), 403);

        $validated = $this->validate([
            'newSupplierId' => 'required|exists:suppliers,id',
            'newBillDate' => 'required|date',
            'newDueDate' => 'nullable|date|after_or_equal:newBillDate',
            'newBillTotal' => 'required|numeric|min:0.01',
            'newDescription' => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::findOrFail((int) $validated['newSupplierId']);

        Bill::create([
            'branch_id' => $supplier->branch_id ?? session('current_branch_id'),
            'supplier_id' => $supplier->id,
            'created_by' => Auth::id(),
            'subtotal' => (float) $validated['newBillTotal'],
            'total' => (float) $validated['newBillTotal'],
            'balance_due' => (float) $validated['newBillTotal'],
            'status' => 'draft',
            'bill_date' => $validated['newBillDate'],
            'due_date' => $validated['newDueDate'] ?: null,
            'description' => $validated['newDescription'] ?? null,
        ]);

        $this->newSupplierId = '';
        $this->newBillTotal = '';
        $this->newDescription = '';

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Supplier bill submitted for approval.',
        ]);
    }

    public function approveBill(int $billId): void
    {
        abort_unless(Gate::allows('manage_creditors'), 403);

        $bill = Bill::query()
            ->whereIn('branch_id', $this->currentUser()->getAccessibleBranchIds())
            ->where('status', 'draft')
            ->findOrFail($billId);

        $bill->update(['status' => 'received']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Bill approved and ready for payment.',
        ]);
    }

    public function rejectBill(int $billId): void
    {
        abort_unless(Gate::allows('manage_creditors'), 403);

        $bill = Bill::query()
            ->whereIn('branch_id', $this->currentUser()->getAccessibleBranchIds())
            ->where('status', 'draft')
            ->findOrFail($billId);

        $bill->update(['status' => 'cancelled']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Bill rejected.',
        ]);
    }

    public function createStaffPayable(): void
    {
        abort_unless(Gate::allows('pay_commissions') || Gate::allows('approve_commissions'), 403);

        $validated = $this->validate([
            'newStaffUserId' => 'required|exists:users,id',
            'newStaffAmount' => 'required|numeric|min:0.01',
            'newStaffDescription' => 'nullable|string|max:255',
        ]);

        $user = User::query()->findOrFail((int) $validated['newStaffUserId']);

        $branchId = session('current_branch_id') ?: ($this->currentUser()->getAccessibleBranchIds()[0] ?? null);

        if (! $branchId) {
            $this->addError('newStaffUserId', 'Select a branch before creating staff payables.');
            return;
        }

        Commission::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'reference_type' => 'manual_staff_payable',
            'reference_id' => 0,
            'base_amount' => (float) $validated['newStaffAmount'],
            'commission_amount' => (float) $validated['newStaffAmount'],
            'status' => 'pending',
            'notes' => $validated['newStaffDescription'] ?: null,
        ]);

        $this->newStaffUserId = '';
        $this->newStaffAmount = '';
        $this->newStaffDescription = '';

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Staff payable submitted for approval.',
        ]);
    }

    public function approveCommission(int $commissionId): void
    {
        abort_unless(Gate::allows('approve_commissions'), 403);

        $commission = Commission::query()
            ->whereIn('branch_id', $this->currentUser()->getAccessibleBranchIds())
            ->where('status', 'pending')
            ->findOrFail($commissionId);

        $commission->approve($this->currentUser());

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Staff payable approved.',
        ]);
    }

    public function startCommissionPayment(int $commissionId): void
    {
        $this->paymentCommissionId = $commissionId;
        $this->staffPaymentAmount = '';
        $this->staffPaymentReference = '';
    }

    public function recordCommissionPayment(): void
    {
        abort_unless(Gate::allows('pay_commissions'), 403);

        $validated = $this->validate([
            'paymentCommissionId' => 'required|exists:commissions,id',
            'staffPaymentAmount' => 'required|numeric|min:0.01',
            'staffPaymentReference' => 'nullable|string|max:100',
        ]);

        $commission = Commission::query()
            ->whereIn('branch_id', $this->currentUser()->getAccessibleBranchIds())
            ->where('status', 'approved')
            ->findOrFail((int) $validated['paymentCommissionId']);

        if ((float) $validated['staffPaymentAmount'] != (float) $commission->commission_amount) {
            $this->addError('staffPaymentAmount', 'Staff payment must match the approved amount.');
            return;
        }

        $commission->markAsPaid($validated['staffPaymentReference'] ?: null);

        $this->paymentCommissionId = null;
        $this->staffPaymentAmount = '';
        $this->staffPaymentReference = '';

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Staff payable marked as paid.',
        ]);
    }

    public function startPayment(int $billId): void
    {
        $this->paymentBillId = $billId;
        $this->paymentAmount = '';
        $this->paymentMethod = 'cash';
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentReference = '';
    }

    public function recordPayment(): void
    {
        abort_unless(Gate::allows('manage_creditors'), 403);

        $validated = $this->validate([
            'paymentBillId' => 'required|exists:bills,id',
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentMethod' => 'required|in:cash,bank_transfer,mobile_money,cheque,credit_card,other',
            'paymentDate' => 'required|date',
            'paymentReference' => 'nullable|string|max:100',
        ]);

        $bill = Bill::findOrFail((int) $validated['paymentBillId']);
        $amount = (float) $validated['paymentAmount'];

        if ($amount > (float) $bill->balance_due) {
            $this->addError('paymentAmount', 'Payment cannot exceed outstanding balance.');
            return;
        }

        $bill->recordPayment([
            'received_by' => Auth::id(),
            'amount' => $amount,
            'payment_method' => $validated['paymentMethod'],
            'payment_date' => $validated['paymentDate'],
            'reference_number' => $validated['paymentReference'] ?: null,
            'status' => 'completed',
        ]);

        $this->paymentBillId = null;
        $this->paymentAmount = '';
        $this->paymentReference = '';

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Bill payment posted successfully.',
        ]);
    }

    public function getRowsProperty()
    {
        return app(CreditorsAgingService::class)->getRows($this->currentUser(), $this->filters());
    }

    public function getTotalsProperty(): array
    {
        return app(CreditorsAgingService::class)->getTotals($this->rows);
    }

    public function getOpenBillsProperty()
    {
        return Bill::query()
            ->with('supplier:id,name')
            ->whereIn('branch_id', $this->currentUser()->getAccessibleBranchIds())
            ->whereIn('status', ['received', 'partially_paid', 'overdue'])
            ->where('balance_due', '>', 0)
            ->orderByDesc('due_date')
            ->limit(30)
            ->get();
    }

    public function getPendingApprovalBillsProperty()
    {
        return Bill::query()
            ->with('supplier:id,name')
            ->whereIn('branch_id', $this->currentUser()->getAccessibleBranchIds())
            ->where('status', 'draft')
            ->latest('bill_date')
            ->limit(30)
            ->get();
    }

    public function getStaffUsersProperty()
    {
        return User::query()
            ->role(['technician', 'wash-attendant', 'cashier'])
            ->where('vendor_id', $this->currentUser()->vendor_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getPendingStaffPayablesProperty()
    {
        return Commission::query()
            ->with('user:id,name')
            ->whereIn('branch_id', $this->currentUser()->getAccessibleBranchIds())
            ->where('status', 'pending')
            ->latest('created_at')
            ->limit(30)
            ->get();
    }

    public function getApprovedStaffPayablesProperty()
    {
        return Commission::query()
            ->with('user:id,name')
            ->whereIn('branch_id', $this->currentUser()->getAccessibleBranchIds())
            ->where('status', 'approved')
            ->latest('approved_at')
            ->limit(30)
            ->get();
    }

    public function getAvailableBranchesProperty()
    {
        return Branch::query()
            ->whereIn('id', $this->currentUser()->getAccessibleBranchIds())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getSuppliersProperty()
    {
        return Supplier::query()
            ->where(function ($query) {
                $query->whereIn('branch_id', $this->currentUser()->getAccessibleBranchIds())
                    ->orWhereNull('branch_id');
            })
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);
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
        return view('livewire.reports.creditors');
    }
}
