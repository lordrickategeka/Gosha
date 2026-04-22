<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

class ExpensesComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = ['category' => ['except' => '']];

    public function render()
    {
        $expenses = Expense::with(['category', 'recordedBy', 'approvedBy'])
            ->when(session('current_branch_id'), fn($q) => $q->where('branch_id', session('current_branch_id')))
            ->when($this->search, fn($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->dateFrom, fn($q) => $q->whereDate('expense_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('expense_date', '<=', $this->dateTo))
            ->latest('expense_date')
            ->paginate(20);

        $totals = [
            'today' => Expense::whereDate('expense_date', today())->sum('amount'),
            'month' => Expense::whereMonth('expense_date', now()->month)->sum('amount'),
        ];

        $categories = ['utilities', 'supplies', 'maintenance', 'salaries', 'rent', 'transport', 'other'];

        return view('livewire.expenses.expenses-component', compact('expenses', 'totals', 'categories'))
            ->layout('components.layouts.app', ['title' => 'Expenses']);
    }
}
