<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseAttachment;
use App\Models\Currency;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseService
{
    public function __construct(
        protected ExpenseApprovalService $approvalService
    ) {}

    /**
     * Create a new expense
     */
    public function createExpense(array $data, ?array $attachments = null): Expense
    {
        return DB::transaction(function () use ($data, $attachments) {
            // Calculate tax if provided
            if (isset($data['tax_percentage']) && $data['tax_percentage'] > 0) {
                if ($data['tax_inclusive'] ?? false) {
                    // Tax is included in amount
                    $data['tax_amount'] = $data['amount'] * ($data['tax_percentage'] / (100 + $data['tax_percentage']));
                } else {
                    // Tax is additional
                    $data['tax_amount'] = $data['amount'] * ($data['tax_percentage'] / 100);
                }
            }

            // Set vendor_id only when the schema supports it.
            if (Expense::hasVendorIdColumn()) {
                $data['vendor_id'] = auth()->user()->vendor_id;
            } else {
                unset($data['vendor_id']);
            }
            $data['created_by'] = auth()->id();

            // Get exchange rate if not UGX
            if (($data['currency'] ?? 'UGX') !== 'UGX' && !isset($data['exchange_rate'])) {
                $data['exchange_rate'] = $this->getExchangeRate($data['currency'], 'UGX');
            }

            // Create expense
            $expense = Expense::create($data);

            // Handle attachments
            if ($attachments) {
                $this->handleAttachments($expense, $attachments);
            }

            // Initialize approval process if not draft
            if ($expense->status === 'pending_approval') {
                $this->approvalService->initializeApprovalProcess($expense);
            }

            return $expense->fresh();
        });
    }

    /**
     * Update an existing expense
     */
    public function updateExpense(Expense $expense, array $data, ?array $attachments = null): Expense
    {
        return DB::transaction(function () use ($expense, $data, $attachments) {
            // Only allow updates if draft or pending
            if (!in_array($expense->status, ['draft', 'pending_approval'])) {
                throw new \Exception('Cannot update expense with status: ' . $expense->status);
            }

            // Recalculate tax if needed
            if (isset($data['tax_percentage']) && $data['tax_percentage'] > 0) {
                if ($data['tax_inclusive'] ?? $expense->tax_inclusive) {
                    $data['tax_amount'] = $data['amount'] * ($data['tax_percentage'] / (100 + $data['tax_percentage']));
                } else {
                    $data['tax_amount'] = $data['amount'] * ($data['tax_percentage'] / 100);
                }
            }

            // Update exchange rate if currency changed
            if (isset($data['currency']) && $data['currency'] !== $expense->currency && $data['currency'] !== 'UGX') {
                $data['exchange_rate'] = $this->getExchangeRate($data['currency'], 'UGX');
            }

            $expense->update($data);

            // Handle new attachments
            if ($attachments) {
                $this->handleAttachments($expense, $attachments);
            }

            return $expense->fresh();
        });
    }

    /**
     * Submit expense for approval
     */
    public function submitForApproval(Expense $expense): void
    {
        if ($expense->status !== 'draft') {
            throw new \Exception('Only draft expenses can be submitted for approval');
        }

        $expense->update(['status' => 'pending_approval']);
        $this->approvalService->initializeApprovalProcess($expense);
    }

    /**
     * Delete an expense
     */
    public function deleteExpense(Expense $expense): void
    {
        // Only allow deletion of draft or rejected expenses
        if (!in_array($expense->status, ['draft', 'rejected'])) {
            throw new \Exception('Cannot delete expense with status: ' . $expense->status);
        }

        DB::transaction(function () use ($expense) {
            // Delete attachments (will trigger file deletion via model event)
            $expense->attachments()->delete();

            // Soft delete expense
            $expense->delete();
        });
    }

    /**
     * Handle file attachments
     */
    protected function handleAttachments(Expense $expense, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $vendorId = $expense->vendor_id ?? auth()->user()->vendor_id ?? 'unknown';
                $path = $file->store('expenses/' . $vendorId . '/' . date('Y/m'), 'public');

                ExpenseAttachment::create([
                    'expense_id' => $expense->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'attachment_type' => $this->guessAttachmentType($file),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Guess attachment type from file
     */
    protected function guessAttachmentType(UploadedFile $file): string
    {
        $name = strtolower($file->getClientOriginalName());

        if (str_contains($name, 'receipt')) {
            return 'receipt';
        }
        if (str_contains($name, 'invoice')) {
            return 'invoice';
        }
        if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg'])) {
            return 'photo';
        }

        return 'supporting_doc';
    }

    /**
     * Get exchange rate from one currency to another
     */
    protected function getExchangeRate(string $from, string $to): float
    {
        // For now, return 1.0
        // In production, you'd fetch from exchange_rates table or external API

        // Example implementation:
        // $fromCurrency = Currency::where('code', $from)->first();
        // $toCurrency = Currency::where('code', $to)->first();
        // $rate = ExchangeRate::where('from_currency_id', $fromCurrency->id)
        //     ->where('to_currency_id', $toCurrency->id)
        //     ->latest('effective_date')
        //     ->first();
        // return $rate?->rate ?? 1.0;

        return 1.0;
    }

    /**
     * Get expense statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = Expense::query();
        $reportingAmountColumn = Expense::reportingAmountColumn();

        // Apply vendor filter
        if (auth()->user()->vendor_id) {
            $query->forVendor(auth()->user()->vendor_id);
        }

        // Apply branch filter
        if ($branchId = $filters['branch_id'] ?? session('current_branch_id')) {
            $query->where('branch_id', $branchId);
        }

        // Apply date range
        if ($from = $filters['date_from'] ?? null) {
            $query->whereDate('expense_date', '>=', $from);
        }
        if ($to = $filters['date_to'] ?? null) {
            $query->whereDate('expense_date', '<=', $to);
        }

        return [
            'total' => $query->sum($reportingAmountColumn),
            'today' => (clone $query)->today()->sum($reportingAmountColumn),
            'this_month' => (clone $query)->thisMonth()->sum($reportingAmountColumn),
            'this_year' => (clone $query)->thisYear()->sum($reportingAmountColumn),
            'pending_approval' => (clone $query)->pendingApproval()->count(),
            'approved_pending_payment' => (clone $query)->approved()->sum($reportingAmountColumn),
            'by_category' => (clone $query)->with('category')
                ->get()
                ->groupBy('category_id')
                ->map(fn($items) => [
                    'category' => $items->first()->category?->name ?? 'Uncategorized',
                    'total' => $items->sum($reportingAmountColumn),
                    'count' => $items->count(),
                ]),
            'by_type' => (clone $query)->get()
                ->groupBy('expense_type')
                ->map(fn($items) => [
                    'type' => ucfirst(str_replace('_', ' ', $items->first()->expense_type)),
                    'total' => $items->sum($reportingAmountColumn),
                    'count' => $items->count(),
                ]),
        ];
    }

    /**
     * Export expenses to array for reporting
     */
    public function exportExpenses(array $filters = []): array
    {
        $query = Expense::with(['category', 'createdBy', 'approvedBy', 'branch']);

        // Apply vendor filter
        if (auth()->user()->vendor_id) {
            $query->forVendor(auth()->user()->vendor_id);
        }

        // Apply filters
        if ($branchId = $filters['branch_id'] ?? null) {
            $query->where('branch_id', $branchId);
        }
        if ($categoryId = $filters['category_id'] ?? null) {
            $query->where('category_id', $categoryId);
        }
        if ($type = $filters['expense_type'] ?? null) {
            $query->where('expense_type', $type);
        }
        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }
        if ($from = $filters['date_from'] ?? null) {
            $query->whereDate('expense_date', '>=', $from);
        }
        if ($to = $filters['date_to'] ?? null) {
            $query->whereDate('expense_date', '<=', $to);
        }

        return $query->get()->map(function ($expense) {
            return [
                'Expense Number' => $expense->expense_number,
                'Date' => $expense->expense_date->format('Y-m-d'),
                'Type' => ucfirst(str_replace('_', ' ', $expense->expense_type)),
                'Category' => $expense->category?->name,
                'Description' => $expense->description,
                'Amount' => $expense->amount,
                'Currency' => $expense->currency,
                'Tax Amount' => $expense->tax_amount,
                'Total' => $expense->total_amount,
                'Payment Method' => ucfirst(str_replace('_', ' ', $expense->payment_method)),
                'Status' => ucfirst($expense->status),
                'Branch' => $expense->branch?->name,
                'Created By' => $expense->createdBy?->name,
                'Created At' => $expense->created_at->format('Y-m-d H:i'),
                'Approved By' => $expense->approvedBy?->name,
            ];
        })->toArray();
    }
}
