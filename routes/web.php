<?php

use App\Domains\Organization\Livewire\Dashboard\DashboardComponent;
use App\Domains\Organization\Livewire\Dashboard\Index as Dashboard;
use App\Domains\Operations\Livewire\WorkOrders\WorkOrdersComponent;
use Illuminate\Support\Facades\Route;

use App\Domains\ServiceConfig\Livewire\Templates\TemplateList;
use App\Domains\ServiceConfig\Livewire\Templates\CreateTemplate;
use App\Domains\ServiceConfig\Livewire\Templates\EditTemplate;

use App\Domains\ServiceConfig\Livewire\QualityCheck\QualityCheckFormComponent;
use App\Domains\ServiceConfig\Livewire\QualityCheck\QualityCheckViewComponent;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes (Laravel Breeze or custom)
Route::middleware('guest')->group(function () {
    Route::get('login', \App\Domains\HR\Livewire\Auth\Login::class)->name('login');
    Route::get(' ', \App\Domains\HR\Livewire\Auth\VendorRegistration::class)->name('register');


    });

    // Include expense module routes
require __DIR__.'/expenses.php'; 

Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Force Password Change (must be before other auth routes)
    Route::get('/password/change', \App\Domains\HR\Livewire\Auth\ForcePasswordChange::class)->name('password.change');

    // Dashboard
    Route::get('/dashboard', DashboardComponent::class)->name('dashboard');

    // Branch switching
    Route::get('/branch/{branch}/switch', function (\App\Domains\Organization\Models\Branch $branch) {
        if (!auth()->user()->canAccessBranch($branch)) {
            abort(403);
        }
        session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name]);
        return back();
    })->name('branch.switch');

    // Profile
    Route::get('/profile', \App\Domains\HR\Livewire\Profile\EditProfileComponent::class)->name('profile');

    // // Settings
    Route::get('/settings', \App\Domains\Organization\Livewire\Settings\SettingsComponent::class)->name('settings')
        ->middleware('can:view_settings');

    // Work Orders
    Route::prefix('work-orders')->name('work-orders.')->group(function () {
        Route::get('/', WorkOrdersComponent::class)->name('index');

        Route::get('/create', \App\Domains\Operations\Livewire\WorkOrders\CreateWorkOrdersComponent::class)->name('create')->middleware('can:create_work_orders');
        Route::get('/{workOrder}', \App\Domains\Operations\Livewire\WorkOrders\ShowWorkOrdersComponent::class)->name('show');
        Route::get('/{workOrder}/edit', \App\Domains\Operations\Livewire\WorkOrders\EditWorkOrdersComponent::class)->name('edit')
            ->middleware('can:edit_work_orders');

        // Quotation builder (scoped under work order)
        Route::get('/{workOrder}/quotations/create', \App\Domains\Finance\Livewire\Quotations\QuotationBuilderComponent::class)
            ->name('quotations.create')
            ->middleware('can:create_quotations');
    });

    // Quotations (standalone)
    Route::prefix('quotations')->name('quotations.')->middleware('can:view_quotations')->group(function () {
        Route::get('/', \App\Domains\Finance\Livewire\Quotations\QuotationsComponent::class)->name('index');
        Route::get('/{quotation}', \App\Domains\Finance\Livewire\Quotations\ShowQuotationComponent::class)->name('show');
        Route::get('/{quotation}/edit', \App\Domains\Finance\Livewire\Quotations\QuotationBuilderComponent::class)->name('edit')
            ->middleware('can:edit_quotations');
        Route::get('/{quotation}/pdf', [\App\Http\Controllers\QuotationPdfController::class, 'download'])->name('pdf');
    });

    // Wash Orders ==> washing bays.
    Route::prefix('wash-orders')->name('wash-orders.')->group(function () {
        Route::get('/', \App\Domains\Operations\Livewire\WashOrders\WashOrdersComponent::class)->name('index');
        Route::get('/create', \App\Domains\Operations\Livewire\WashOrders\CreateWashOrdersComponent::class)->name('create')
            ->middleware('can:create_wash_orders');
        Route::get('/{washOrder}', \App\Domains\Operations\Livewire\WashOrders\ShowWashOrdersComponent::class)->name('show');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->middleware('can:view_customers')->group(function () {
        Route::get('/', \App\Domains\CRM\Livewire\Customers\CustomersComponent::class)->name('index');
        Route::get('/create', \App\Domains\CRM\Livewire\Customers\CreateCustomersComponent::class)->name('create')
            ->middleware('can:create_customers');
        Route::get('/{customer}', \App\Domains\CRM\Livewire\Customers\ShowCustomersComponent::class)->name('show');
        Route::get('/{customer}/edit', \App\Domains\CRM\Livewire\Customers\EditCustomersComponent::class)->name('edit')
            ->middleware('can:edit_customers');
    });

    // // Vehicles
    Route::prefix('vehicles')->name('vehicles.')->middleware('can:view_vehicles')->group(function () {
        Route::get('/', \App\Domains\Vehicles\Livewire\Vehicles\VehiclesComponent::class)->name('index');
        Route::get('/create', \App\Domains\Vehicles\Livewire\Vehicles\CreateVehiclesComponent::class)->name('create')
            ->middleware('can:create_vehicles');
        Route::get('/{vehicle}', \App\Domains\Vehicles\Livewire\Vehicles\ShowVehiclesComponent::class)->name('show');
        Route::get('/{vehicle}/edit', \App\Domains\Vehicles\Livewire\Vehicles\EditVehiclesComponent::class)->name('edit')
            ->middleware('can:edit_vehicles');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->middleware('can:view_invoices')->group(function () {
        Route::get('/', \App\Domains\Finance\Livewire\Invoices\InvoicesComponent::class)->name('index');
        Route::get('/create', \App\Domains\Finance\Livewire\Invoices\CreateInvoicesComponent::class)->name('create')
            ->middleware('can:create_invoices');
        Route::get('/{invoice}', \App\Domains\Finance\Livewire\Invoices\ShowInvoicesComponent::class)->name('show');
        // Route::get('/{invoice}/edit', \App\Domains\Finance\Livewire\Invoices\EditInvoicesComponent::class)->name('edit')
        //     ->middleware('can:edit_invoices');
    });

    // // Payments
    Route::prefix('payments')->name('payments.')->middleware('can:view_payments')->group(function () {
        Route::get('/', \App\Domains\Finance\Livewire\Payments\PaymentsComponent::class)->name('index');
    });

    // // Expenses
    // Route::prefix('expenses')->name('expenses.')->middleware('can:view_expenses')->group(function () {
    //     Route::get('/', \App\Domains\Expenses\Livewire\Expenses\ExpensesComponent::class)->name('index');
    //     Route::get('/create', \App\Domains\Expenses\Livewire\Expenses\CreateExpensesComponent::class)->name('create')
    //         ->middleware('can:create_expenses');
    // });

// // Commissions
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', \App\Domains\Commissions\Livewire\Commissions\CommissionsComponent::class)->name('index');
        Route::get('/my', \App\Domains\Commissions\Livewire\Commissions\MyCommissionsComponent::class)->name('my')
            ->middleware('can:view_own_commissions');
    });

    // // Appointments
    Route::prefix('appointments')->name('appointments.')->middleware('can:view_appointments')->group(function () {
        Route::get('/', \App\Domains\CRM\Livewire\Appointments\AppointmentsComponent::class)->name('index');
        Route::get('/create', \App\Domains\CRM\Livewire\Appointments\CreateAppointmentsComponent::class)->name('create')
            ->middleware('can:create_appointments');
    });

    // // Calendar
    Route::get('/calendar', \App\Domains\CRM\Livewire\Calendar\CalendarComponent::class)
        ->name('calendar');

    // // Bay Status
    Route::get('/bays', \App\Domains\Operations\Livewire\Bays\BayStatusComponent::class)->name('bays.status');

    // // Inventory
    Route::prefix('inventory')->name('inventory.')->middleware('can:view_inventory')->group(function () {
        Route::get('/', \App\Domains\Inventory\Livewire\Inventory\InventoryComponent::class)->name('index');
        Route::get('/low-stock', \App\Domains\Inventory\Livewire\Inventory\LowStock::class)->name('low-stock');
        Route::get('/movements', \App\Domains\Inventory\Livewire\Inventory\Movements::class)->name('movements')
            ->middleware('can:adjust_stock');
        Route::get('/create', \App\Domains\Inventory\Livewire\Inventory\CreateInventoryComponent::class)->name('create')
            ->middleware('can:create_inventory');
        Route::get('/{inventoryItem}', \App\Domains\Inventory\Livewire\Inventory\ShowInventoryComponent::class)->name('show');
        Route::get('/{inventoryItem}/edit', \App\Domains\Inventory\Livewire\Inventory\EditInventoryComponent::class)->name('edit')
            ->middleware('can:edit_inventory');
    });

    // // Suppliers
    Route::prefix('suppliers')->name('suppliers.')->middleware('can:view_suppliers')->group(function () {
        Route::get('/', \App\Domains\Inventory\Livewire\Suppliers\SuppliersComponent::class)->name('index');
        Route::get('/create', \App\Domains\Inventory\Livewire\Suppliers\CreateSuppliersComponent::class)->name('create')
            ->middleware('can:create_suppliers');
        Route::get('/{supplier}', \App\Domains\Inventory\Livewire\Suppliers\ShowSuppliersComponent::class)->name('show');
    });

    // // Staff / Users
    Route::prefix('users')->name('users.')->middleware('can:view_users')->group(function () {
        Route::get('/', \App\Domains\HR\Livewire\Users\UsersComponent::class)->name('index');
        Route::get('/create', \App\Domains\HR\Livewire\Users\CreateUsersComponent::class)->name('create')
            ->middleware('can:create_users');
        Route::get('/{user}', \App\Domains\HR\Livewire\Users\ShowUsersComponent::class)->name('show');
        Route::get('/{user}/edit', \App\Domains\HR\Livewire\Users\EditUsersComponent::class)->name('edit')
            ->middleware('can:edit_users');
    });

    // // Branches
    Route::prefix('branches')->name('branches.')->middleware('can:view_branches')->group(function () {
        Route::get('/', \App\Domains\Organization\Livewire\Branches\BranchesComponent::class)->name('index');
        Route::get('/create', \App\Domains\Organization\Livewire\Branches\CreateBranchesComponent::class)->name('create')
            ->middleware('can:create_branches');
        Route::get('/{branch}/edit', \App\Domains\Organization\Livewire\Branches\EditBranchesComponent::class)->name('edit')
            ->middleware('can:edit_branches');
    });

    // // Templates (Service Templates & Wash Packages)
    Route::prefix('templates')->name('templates.')->middleware('can:view_service_templates')->group(function () {
        Route::get('/', \App\Domains\ServiceConfig\Livewire\Templates\TemplatesComponent::class)->name('index');
    });

    // // Service Types
    Route::get('/service-types', \App\Domains\ServiceConfig\Livewire\ServiceTypesComponent::class)
        ->name('service-types.index')
        ->middleware('can:view_service_templates');

    // // Service Categories
    Route::get('/service-categories', \App\Domains\ServiceConfig\Livewire\ServiceCategoriesComponent::class)
        ->name('service-categories.index')
        ->middleware('can:view_service_templates');

    // // Packages (Wash Packages)
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', \App\Domains\ServiceConfig\Livewire\Packages\PackagesComponent::class)->name('index');
    });

    // // Reports
    Route::prefix('reports')->name('reports.')->middleware('can:view_reports')->group(function () {
        Route::get('/sales', \App\Domains\Organization\Livewire\Reports\Sales::class)->name('sales');
        Route::get('/operations', \App\Domains\Organization\Livewire\Reports\Operations::class)->name('operations');
        Route::get('/inventory', \App\Domains\Organization\Livewire\Reports\Inventory::class)->name('inventory');
        Route::get('/staff', \App\Domains\Organization\Livewire\Reports\Staff::class)->name('staff');
        Route::get('/debtors', \App\Domains\Organization\Livewire\Reports\Debtors::class)->name('debtors')
            ->middleware('can:view_debtors');
        Route::get('/creditors', \App\Domains\Organization\Livewire\Reports\Creditors::class)->name('creditors')
            ->middleware('can:view_creditors');
    });

    // Roles & Permissions
    Route::prefix('roles')->name('roles.')->middleware('can:manage_roles')->group(function () {
        Route::get('/', \App\Domains\HR\Livewire\Roles\RolesComponent::class)->name('index');
        Route::get('/{roleId}/edit', \App\Domains\HR\Livewire\Roles\EditRoleComponent::class)->name('edit');
    });

    // // Platform Admin Routes
    Route::prefix('platform')->name('platform.')->group(function () {
        Route::get('/vendors', \App\Domains\Platform\Livewire\Platform\Vendors\Index::class)->name('vendors.index');
        Route::get('/vendors/create', \App\Domains\Platform\Livewire\Platform\Vendors\Create::class)->name('vendors.create');
        Route::get('/vendors/{vendor}', \App\Domains\Platform\Livewire\Platform\Vendors\Show::class)->name('vendors.show');
        Route::get('/billing', \App\Domains\Platform\Livewire\Platform\Billing::class)->name('billing');
        Route::get('/integrations', \App\Domains\Platform\Livewire\Platform\ApiIntegrationsComponent::class)->name('integrations.index');
    });

    // new vendor -platform -pricing
    Route::get('/platform-pricing', \App\Domains\Platform\Livewire\Platform\PlansComponent::class)->name('platform.pricing');

    // Template management
    // Route::get('/templates', TemplateList::class)
    //     ->name('templates.index');

    // Route::get('/templates/create', CreateTemplate::class)
    //     ->name('templates.create');

    // Route::get('/templates/{template}/edit', EditTemplate::class)
    //     ->name('templates.edit');

    // // Template preview (for testing)
    // Route::get('/templates/{template}/preview', [TemplateController::class, 'preview'])
    //     ->name('templates.preview');

    // Notifications
    Route::get('/notifications', \App\Domains\Notifications\Livewire\NotificationCenter::class)->name('notifications.index');

    // Quality Check Routes
    Route::prefix('quality-checks')->name('quality-checks.')->group(function () {
        Route::get('work-order/{workOrder}', QualityCheckFormComponent::class)
            ->name('create')
            ->can('quality-check.create');

        Route::get('{qualityCheck}', QualityCheckViewComponent::class)
            ->name('show')
            ->can('quality-check.view');

        Route::get('{qualityCheck}/edit', QualityCheckFormComponent::class)
            ->name('edit')
            ->can('quality-check.update');
    });

});

// ─── Public Quotation Routes (no auth) ───────────────────────────────────────
Route::get('/q/{token}', [\App\Http\Controllers\PublicQuotationController::class, 'show'])
    ->name('quotations.public');
Route::post('/q/{token}/approve', [\App\Http\Controllers\PublicQuotationController::class, 'approve'])
    ->name('quotations.public.approve');
Route::post('/q/{token}/reject', [\App\Http\Controllers\PublicQuotationController::class, 'reject'])
    ->name('quotations.public.reject');

// ─── Public Debit Note Routes (no auth) ──────────────────────────────────────
Route::get('/dn/{token}', [\App\Http\Controllers\PublicDebitNoteController::class, 'show'])
    ->name('debit-notes.public');
Route::post('/dn/{token}/submit', [\App\Http\Controllers\PublicDebitNoteController::class, 'submit'])
    ->name('debit-notes.public.submit');

