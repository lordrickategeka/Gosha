<?php

use App\Livewire\Expenses\ExpenseList;
use App\Livewire\Expenses\ExpenseCreate;
use App\Livewire\Expenses\ExpenseEdit;
use App\Livewire\Expenses\ExpenseView;
use App\Livewire\Expenses\ExpenseApprovals;
use App\Livewire\Expenses\Categories\ExpenseCategoryList;
use App\Livewire\Expenses\Categories\ExpenseCategoryForm;
use App\Livewire\Expenses\ApprovalChains\ApprovalChainList;
use App\Livewire\Expenses\ApprovalChains\ApprovalChainBuilder;
use App\Livewire\Expenses\ApprovalChains\ApprovalChainEdit;
use App\Livewire\Expenses\Reports\ExpenseReports;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Expense Module Routes
|--------------------------------------------------------------------------
|
| All routes for the expense management module including:
| - Expense CRUD operations
| - Approval workflows
| - Category management
| - Approval chain configuration
| - Reports and analytics
|
*/

Route::middleware(['auth'])->group(function () {

    Route::prefix('expenses')->name('expenses.')->group(function () {

        // Main Expense Routes
        Route::middleware(['can:view_expenses'])->group(function () {
            Route::get('/', ExpenseList::class)->name('index');
            Route::get('/{expense}', ExpenseView::class)->whereNumber('expense')->name('view');
        });

        Route::middleware(['can:create_expenses'])->group(function () {
            Route::get('/create', ExpenseCreate::class)->name('create');
            });

        // Route::middleware(['can:edit_expenses'])->group(function () {
        //     Route::get('/{expense}/edit', ExpenseEdit::class)->name('edit');
        // });

        // Approval Routes
        // Route::prefix('approvals')->name('approvals.')->group(function () {
        //     Route::middleware(['can:approve_expenses'])->group(function () {
        //         Route::get('/pending', ExpenseApprovals::class)->name('pending');
        //     });
        // });

        // Category Management Routes
        // Route::prefix('categories')->name('categories.')->middleware(['can:manage_expense_categories'])->group(function () {
        //     Route::get('/', ExpenseCategoryList::class)->name('index');
        //     Route::get('/create', ExpenseCategoryForm::class)->name('create');
        //     Route::get('/{category}/edit', ExpenseCategoryForm::class)->name('edit');
        // });

        // Approval Chain Management Routes
        // Route::prefix('approval-chains')->name('approval-chains.')->middleware(['can:manage_approval_chains'])->group(function () {
        //     Route::get('/', ApprovalChainList::class)->name('index');
        //     Route::get('/create', ApprovalChainBuilder::class)->name('create');
        //     Route::get('/{chain}/edit', ApprovalChainEdit::class)->name('edit');
        // });

        // Reports Routes
        // Route::prefix('reports')->name('reports.')->middleware(['can:view_expenses'])->group(function () {
        //     Route::get('/', ExpenseReports::class)->name('index');
        // });
    });
});
