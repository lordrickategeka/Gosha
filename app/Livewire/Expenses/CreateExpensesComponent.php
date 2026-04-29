<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Livewire\Component;

class CreateExpensesComponent extends Component
{
    public $description = '';
    public $amount = '';
    public $category_id = '';
    public $expense_date = '';
    public $payment_method = 'cash';
    public $reference = '';
    public $notes = '';

    protected $rules = [
        'description' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'category_id' => 'required|exists:expense_categories,id',
        'expense_date' => 'required|date',
        'payment_method' => 'required|in:cash,mobile_money,bank_transfer,card',
    ];

    public $branchId;

    public function mount()
    {
        $this->branchId = session('current_branch_id')
            ?? auth()->user()?->primaryBranch()?->id
            ?? auth()->user()?->branches()->first()?->id;

        $this->expense_date = today()->format('Y-m-d');
    }

    public function save()
    {
        if (!$this->branchId) {
            session()->flash('error', 'No active branch selected. Please refresh the page or switch branches.');
            return;
        }

        $this->validate();

        Expense::create([
            'branch_id' => $this->branchId,
            'category_id' => $this->category_id,
            'recorded_by' => auth()->id(),
            'description' => $this->description,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'reference' => $this->reference,
            'expense_date' => $this->expense_date,
            'notes' => $this->notes,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Expense recorded successfully.');
        return $this->redirect(route('expenses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.expenses.create-expenses-component', [
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'Add Expense']);
    }
}
