<?php

namespace App\Domains\Expenses\Livewire\Expenses;

use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Organization\Models\Currency;
use App\Domains\Inventory\Models\Supplier;
use App\Domains\Expenses\Services\ExpenseService;
use App\Domains\Expenses\Services\ExpenseApprovalService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ExpenseCreate extends Component
{
    use WithFileUploads;

    // Basic Information
    public $expense_type = 'business';
    public $category_id = '';
    public $supplier_id = '';
    public $expense_date = '';
    public $description = '';

    // Amount & Currency
    public $amount = '';
    public $currency = 'UGX';
    public $exchange_rate = 1.0000;

    // Tax Information
    public $tax_percentage = '';
    public $tax_inclusive = false;

    // Payment Information
    public $payment_method = 'cash';
    public $payment_reference = '';

    // Additional
    public $notes = '';
    public $attachments = [];
    public $save_as_draft = false;

    // Preview
    public $show_approval_preview = false;
    public $approval_preview = null;

    protected function rules()
    {
        return [
            'expense_type' => 'required|in:business,petty_cash,employee_claim,payroll',
            'category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_inclusive' => 'boolean',
            'payment_method' => 'required|in:cash,mobile_money,bank_transfer,card,check',
            'payment_reference' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string|max:1000',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ];
    }

    public function mount()
    {
        $this->expense_date = now()->format('Y-m-d');
    }

    public function updatedCurrency($value)
    {
        if ($value !== 'UGX') {
            // In production, fetch actual exchange rate
            $this->exchange_rate = 1.0000;
        } else {
            $this->exchange_rate = 1.0000;
        }
    }

    public function updatedCategoryId()
    {
        // Reset tax if category changes
        $category = ExpenseCategory::find($this->category_id);
        if ($category && $category->requires_tax_invoice) {
            // Could set default tax percentage here if needed
        }
    }

    public function previewApproval()
    {
        $this->validate([
            'expense_type' => 'required',
            'category_id' => 'required',
            'amount' => 'required|numeric|min:0',
        ]);

        // Create temporary expense object for preview
        $tempExpense = new Expense([
            'vendor_id' => auth()->user()->vendor_id,
            'branch_id' => session('current_branch_id'),
            'expense_type' => $this->expense_type,
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ]);

        $approvalService = app(ExpenseApprovalService::class);
        $this->approval_preview = $approvalService->previewApprovalPath($tempExpense);
        $this->show_approval_preview = true;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'expense_type' => $this->expense_type,
                'category_id' => $this->category_id,
                'supplier_id' => $this->supplier_id ?: null,
                'expense_date' => $this->expense_date,
                'description' => $this->description,
                'amount' => $this->amount,
                'currency' => $this->currency,
                'exchange_rate' => $this->exchange_rate,
                'tax_percentage' => $this->tax_percentage !== '' ? $this->tax_percentage : 0,
                'tax_inclusive' => $this->tax_inclusive,
                'payment_method' => $this->payment_method,
                'payment_reference' => $this->payment_reference,
                'notes' => $this->notes,
                'branch_id' => session('current_branch_id'),
                'status' => $this->save_as_draft ? 'draft' : 'pending_approval',
            ];

            $expenseService = app(ExpenseService::class);
            $expense = $expenseService->createExpense($data, $this->attachments);

            session()->flash('success', $this->save_as_draft
                ? 'Expense saved as draft successfully.'
                : 'Expense submitted for approval successfully.');

            return redirect()->route('expenses.view', $expense);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create expense: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $categories = ExpenseCategory::where('vendor_id', auth()->user()->vendor_id)
            ->when($this->expense_type, fn($q) => $q->where(function($query) {
                $query->whereNull('expense_type')
                      ->orWhere('expense_type', $this->expense_type);
            }))
            ->active()
            ->ordered()
            ->get();

        $currencies = Currency::active()->get();

        $suppliers = Supplier::where('vendor_id', auth()->user()->vendor_id)
            ->active()
            ->orderBy('name')
            ->get();

        $expenseTypes = [
            'business' => 'Business Expense',
            'petty_cash' => 'Petty Cash',
            'employee_claim' => 'Employee Claim',
            'payroll' => 'Payroll',
        ];

        $paymentMethods = [
            'cash' => 'Cash',
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            'check' => 'Check',
        ];

        return view('livewire.expenses.expense-create', [
            'categories' => $categories,
            'currencies' => $currencies,
            'suppliers' => $suppliers,
            'expenseTypes' => $expenseTypes,
            'paymentMethods' => $paymentMethods,
        ])->layout('components.layouts.app', ['title' => 'Create Expense']);
    }
}
