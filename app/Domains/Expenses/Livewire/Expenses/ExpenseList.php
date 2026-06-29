<?php

namespace App\Domains\Expenses\Livewire\Expenses;

use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Services\ExpenseService;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseList extends Component
{
    use WithPagination;

    public $search = '';
    public $expenseType = '';
    public $categoryId = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'expenseType' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingExpenseType()
    {
        $this->resetPage();
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'expenseType', 'categoryId', 'status', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function delete($id)
    {
        $expense = Expense::findOrFail($id);

        // Check permission
        if (!auth()->user()->can('delete_expenses')) {
            session()->flash('error', 'You do not have permission to delete expenses.');
            return;
        }

        try {
            $expenseService = app(ExpenseService::class);
            $expenseService->deleteExpense($expense);

            session()->flash('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $expenses = Expense::with(['category', 'createdBy', 'approvedBy', 'branch'])
            ->forVendor(auth()->user()->vendor_id)
            ->when(session('current_branch_id'), fn($q) => $q->where('branch_id', session('current_branch_id')))
            ->when($this->search, fn($q) => $q->where(function($query) {
                $query->where('description', 'like', "%{$this->search}%")
                      ->orWhere('expense_number', 'like', "%{$this->search}%")
                      ->orWhere('payment_reference', 'like', "%{$this->search}%");
            }))
            ->when($this->expenseType, fn($q) => $q->where('expense_type', $this->expenseType))
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn($q) => $q->whereDate('expense_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('expense_date', '<=', $this->dateTo))
            ->latest('expense_date')
            ->paginate(20);

        $expenseService = app(ExpenseService::class);
        $stats = $expenseService->getStatistics([
            'branch_id' => session('current_branch_id'),
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ]);

        $categories = ExpenseCategory::forVendor(auth()->user()->vendor_id)
            ->active()
            ->ordered()
            ->get();

        $expenseTypes = [
            'business' => 'Business Expense',
            'petty_cash' => 'Petty Cash',
            'employee_claim' => 'Employee Claim',
            'payroll' => 'Payroll',
        ];

        $statuses = [
            'draft' => 'Draft',
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
        ];

        return view('livewire.expenses.expense-list', [
            'expenses' => $expenses,
            'stats' => $stats,
            'categories' => $categories,
            'expenseTypes' => $expenseTypes,
            'statuses' => $statuses,
        ])->layout('components.layouts.app', ['title' => 'Expenses']);
    }
}
