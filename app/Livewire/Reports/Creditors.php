<?php

namespace App\Livewire\Reports;

use App\Models\Bill;
use App\Models\Branch;
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

    public $paymentBillId = null;
    public $paymentAmount = '';
    public $paymentMethod = 'cash';
    public $paymentDate = '';
    public $paymentReference = '';

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
            'status' => 'received',
            'bill_date' => $validated['newBillDate'],
            'due_date' => $validated['newDueDate'] ?: null,
            'description' => $validated['newDescription'] ?? null,
        ]);

        $this->newSupplierId = '';
        $this->newBillTotal = '';
        $this->newDescription = '';

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Supplier bill created successfully.',
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
