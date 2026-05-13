<?php

use App\Livewire\Dashboard\DashboardComponent;
use App\Livewire\Dashboard\Index as Dashboard;
use App\Livewire\WorkOrders\WorkOrdersComponent;
use Illuminate\Support\Facades\Route;

use App\Livewire\Templates\TemplateList;
use App\Livewire\Templates\CreateTemplate;
use App\Livewire\Templates\EditTemplate;

use App\Livewire\QualityCheck\QualityCheckFormComponent;
use App\Livewire\QualityCheck\QualityCheckViewComponent;

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
    Route::get('login', \App\Livewire\Auth\Login::class)->name('login');
    Route::get('register', \App\Livewire\Auth\VendorRegistration::class)->name('register');


    });


Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Force Password Change (must be before other auth routes)
    Route::get('/password/change', \App\Livewire\Auth\ForcePasswordChange::class)->name('password.change');

    // Dashboard
    Route::get('/dashboard', DashboardComponent::class)->name('dashboard');

    // Branch switching
    Route::get('/branch/{branch}/switch', function (\App\Models\Branch $branch) {
        if (!auth()->user()->canAccessBranch($branch)) {
            abort(403);
        }
        session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name]);
        return back();
    })->name('branch.switch');

    // Profile
    Route::get('/profile', \App\Livewire\Profile\EditProfileComponent::class)->name('profile');

    // // Settings
    Route::get('/settings', \App\Livewire\Settings\SettingsComponent::class)->name('settings')
        ->middleware('can:view_settings');

    // Work Orders
    Route::prefix('work-orders')->name('work-orders.')->group(function () {
        Route::get('/', WorkOrdersComponent::class)->name('index');

        Route::get('/create', \App\Livewire\WorkOrders\CreateWorkOrder::class)->name('create')->middleware('can:create_work_orders');
        Route::get('/{workOrder}', \App\Livewire\WorkOrders\ShowWorkOrdersComponent::class)->name('show');
        Route::get('/{workOrder}/edit', \App\Livewire\WorkOrders\EditWorkOrdersComponent::class)->name('edit')
            ->middleware('can:edit_work_orders');

        // Quotation builder (scoped under work order)
        Route::get('/{workOrder}/quotations/create', \App\Livewire\Quotations\QuotationBuilderComponent::class)
            ->name('quotations.create')
            ->middleware('can:create_quotations');
    });

    // Backward-compatible alias used by legacy links/calls in production.
    Route::get('/quotations/create/{workOrder}', function (\App\Models\WorkOrder $workOrder) {
        return redirect()->route('work-orders.quotations.create', $workOrder);
    })->name('quotations.create')->middleware('can:create_quotations');

    // Quotations (standalone)
    Route::prefix('quotations')->name('quotations.')->middleware('can:view_quotations')->group(function () {
        Route::get('/{quotation}', \App\Livewire\Quotations\ShowQuotationComponent::class)->name('show');
        Route::get('/{quotation}/edit', \App\Livewire\Quotations\QuotationBuilderComponent::class)->name('edit')
            ->middleware('can:edit_quotations');
        Route::get('/{quotation}/pdf', [\App\Http\Controllers\QuotationPdfController::class, 'download'])->name('pdf');
    });

    // Wash Orders ==> washing bays.
    Route::prefix('wash-orders')->name('wash-orders.')->group(function () {
        Route::get('/', \App\Livewire\WashOrders\WashOrdersComponent::class)->name('index');
        Route::get('/create', \App\Livewire\WashOrders\CreateWashOrdersComponent::class)->name('create')
            ->middleware('can:create_wash_orders');
        Route::get('/{washOrder}', \App\Livewire\WashOrders\ShowWashOrdersComponent::class)->name('show');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->middleware('can:view_customers')->group(function () {
        Route::get('/', \App\Livewire\Customers\CustomersComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Customers\CreateCustomersComponent::class)->name('create')
            ->middleware('can:create_customers');
        Route::get('/{customer}', \App\Livewire\Customers\ShowCustomersComponent::class)->name('show');
        Route::get('/{customer}/edit', \App\Livewire\Customers\EditCustomersComponent::class)->name('edit')
            ->middleware('can:edit_customers');
    });

    // // Vehicles
    Route::prefix('vehicles')->name('vehicles.')->middleware('can:view_vehicles')->group(function () {
        Route::get('/', \App\Livewire\Vehicles\VehiclesComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Vehicles\CreateVehiclesComponent::class)->name('create')
            ->middleware('can:create_vehicles');
        Route::get('/{vehicle}', \App\Livewire\Vehicles\ShowVehiclesComponent::class)->name('show');
        Route::get('/{vehicle}/edit', \App\Livewire\Vehicles\EditVehiclesComponent::class)->name('edit')
            ->middleware('can:edit_vehicles');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->middleware('can:view_invoices')->group(function () {
        Route::get('/', \App\Livewire\Invoices\InvoicesComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Invoices\CreateInvoicesComponent::class)->name('create')
            ->middleware('can:create_invoices');
        Route::get('/{invoice}', \App\Livewire\Invoices\ShowInvoicesComponent::class)->name('show');
        // Route::get('/{invoice}/edit', \App\Livewire\Invoices\EditInvoicesComponent::class)->name('edit')
        //     ->middleware('can:edit_invoices');
    });

    // // Payments
    Route::prefix('payments')->name('payments.')->middleware('can:view_payments')->group(function () {
        Route::get('/', \App\Livewire\Payments\PaymentsComponent::class)->name('index');
    });

    // // Expenses
    Route::prefix('expenses')->name('expenses.')->middleware('can:view_expenses')->group(function () {
        Route::get('/', \App\Livewire\Expenses\ExpensesComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Expenses\CreateExpensesComponent::class)->name('create')
            ->middleware('can:create_expenses');
    });

    // // Commissions
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', \App\Livewire\Commissions\CommissionsComponent::class)->name('index');
    });

    // // Appointments
    Route::prefix('appointments')->name('appointments.')->middleware('can:view_appointments')->group(function () {
        Route::get('/', \App\Livewire\Appointments\AppointmentsComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Appointments\CreateAppointmentsComponent::class)->name('create')
            ->middleware('can:create_appointments');
    });

    // // Calendar
    Route::get('/calendar', \App\Livewire\Calendar\CalendarComponent::class)
        ->name('calendar');

    // // Bay Status
    Route::get('/bays', \App\Livewire\Bays\BayStatusComponent::class)->name('bays.status');

    // // Inventory
    Route::prefix('inventory')->name('inventory.')->middleware('can:view_inventory')->group(function () {
        Route::get('/', \App\Livewire\Inventory\InventoryComponent::class)->name('index');
        Route::get('/low-stock', \App\Livewire\Inventory\LowStock::class)->name('low-stock');
        Route::get('/movements', \App\Livewire\Inventory\Movements::class)->name('movements')
            ->middleware('can:adjust_stock');
        Route::get('/create', \App\Livewire\Inventory\CreateInventoryComponent::class)->name('create')
            ->middleware('can:create_inventory');
        Route::get('/{inventoryItem}', \App\Livewire\Inventory\ShowInventoryComponent::class)->name('show');
        Route::get('/{inventoryItem}/edit', \App\Livewire\Inventory\EditInventoryComponent::class)->name('edit')
            ->middleware('can:edit_inventory');
    });

    // // Suppliers
    Route::prefix('suppliers')->name('suppliers.')->middleware('can:view_suppliers')->group(function () {
        Route::get('/', \App\Livewire\Suppliers\SuppliersComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Suppliers\CreateSuppliersComponent::class)->name('create')
            ->middleware('can:create_suppliers');
        Route::get('/{supplier}', \App\Livewire\Suppliers\ShowSuppliersComponent::class)->name('show');
    });

    // // Staff / Users
    Route::prefix('users')->name('users.')->middleware('can:view_users')->group(function () {
        Route::get('/', \App\Livewire\Users\UsersComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Users\CreateUsersComponent::class)->name('create')
            ->middleware('can:create_users');
        Route::get('/{user}', \App\Livewire\Users\ShowUsersComponent::class)->name('show');
        Route::get('/{user}/edit', \App\Livewire\Users\EditUsersComponent::class)->name('edit')
            ->middleware('can:edit_users');
    });

    // // Branches
    Route::prefix('branches')->name('branches.')->middleware('can:view_branches')->group(function () {
        Route::get('/', \App\Livewire\Branches\BranchesComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Branches\CreateBranchesComponent::class)->name('create')
            ->middleware('can:create_branches');
        Route::get('/{branch}/edit', \App\Livewire\Branches\EditBranchesComponent::class)->name('edit')
            ->middleware('can:edit_branches');
    });

    // // Templates (Service Templates & Wash Packages)
    Route::prefix('templates')->name('templates.')->middleware('can:view_service_templates')->group(function () {
        Route::get('/', \App\Livewire\Templates\TemplatesComponent::class)->name('index');
    });

    // // Service Types
    Route::get('/service-types', \App\Livewire\ServiceTypesComponent::class)
        ->name('service-types.index')
        ->middleware('can:view_service_templates');

    // // Service Categories
    Route::get('/service-categories', \App\Livewire\ServiceCategoriesComponent::class)
        ->name('service-categories.index')
        ->middleware('can:view_service_templates');

    // // Packages (Wash Packages)
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', \App\Livewire\Packages\PackagesComponent::class)->name('index');
    });

    // // Reports
    Route::prefix('reports')->name('reports.')->middleware('can:view_reports')->group(function () {
        Route::get('/sales', \App\Livewire\Reports\Sales::class)->name('sales');
        Route::get('/operations', \App\Livewire\Reports\Operations::class)->name('operations');
        Route::get('/inventory', \App\Livewire\Reports\Inventory::class)->name('inventory');
        Route::get('/staff', \App\Livewire\Reports\Staff::class)->name('staff');
    });

    // Roles & Permissions
    Route::prefix('roles')->name('roles.')->middleware('can:manage_roles')->group(function () {
        Route::get('/', \App\Livewire\Roles\RolesComponent::class)->name('index');
        Route::get('/{roleId}/edit', \App\Livewire\Roles\EditRoleComponent::class)->name('edit');
    });

    // // Platform Admin Routes
    Route::prefix('platform')->name('platform.')->group(function () {
        Route::get('/vendors', \App\Livewire\Platform\Vendors\Index::class)->name('vendors.index');
        Route::get('/vendors/create', \App\Livewire\Platform\Vendors\Create::class)->name('vendors.create');
        Route::get('/vendors/{vendor}', \App\Livewire\Platform\Vendors\Show::class)->name('vendors.show');
        Route::get('/billing', \App\Livewire\Platform\Billing::class)->name('billing');
        Route::get('/integrations', \App\Livewire\Platform\ApiIntegrationsComponent::class)->name('integrations.index');
    });

    // new vendor -platform -pricing
    Route::get('/platform-pricing', \App\Livewire\Platform\PlansComponent::class)->name('platform.pricing');

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

