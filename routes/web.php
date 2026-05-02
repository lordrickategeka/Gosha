<?php

use App\Livewire\Dashboard\DashboardComponent;
use App\Livewire\Dashboard\Index as Dashboard;
use App\Livewire\WorkOrders\WorkOrdersComponent;
use Illuminate\Support\Facades\Route;

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
        ->middleware('can:view settings');

    // Work Orders
    Route::prefix('work-orders')->name('work-orders.')->group(function () {
        Route::get('/', WorkOrdersComponent::class)->name('index');
        Route::get('/create', \App\Livewire\WorkOrders\CreateWorkOrdersComponent::class)->name('create')
            ->middleware('can:create work orders');
        Route::get('/{workOrder}', \App\Livewire\WorkOrders\ShowWorkOrdersComponent::class)->name('show');
        Route::get('/{workOrder}/edit', \App\Livewire\WorkOrders\EditWorkOrdersComponent::class)->name('edit')
            ->middleware('can:edit work orders');
    });

    // Wash Orders ==> washing bays.
    Route::prefix('wash-orders')->name('wash-orders.')->group(function () {
        Route::get('/', \App\Livewire\WashOrders\WashOrdersComponent::class)->name('index');
        Route::get('/create', \App\Livewire\WashOrders\CreateWashOrdersComponent::class)->name('create')
            ->middleware('can:create wash orders');
        Route::get('/{washOrder}', \App\Livewire\WashOrders\ShowWashOrdersComponent::class)->name('show');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->middleware('can:view customers')->group(function () {
        Route::get('/', \App\Livewire\Customers\CustomersComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Customers\CreateCustomersComponent::class)->name('create')
            ->middleware('can:create customers');
        Route::get('/{customer}', \App\Livewire\Customers\ShowCustomersComponent::class)->name('show');
        Route::get('/{customer}/edit', \App\Livewire\Customers\EditCustomersComponent::class)->name('edit')
            ->middleware('can:edit customers');
    });

    // // Vehicles
    Route::prefix('vehicles')->name('vehicles.')->middleware('can:view vehicles')->group(function () {
        Route::get('/', \App\Livewire\Vehicles\VehiclesComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Vehicles\CreateVehiclesComponent::class)->name('create')
            ->middleware('can:create vehicles');
        Route::get('/{vehicle}', \App\Livewire\Vehicles\ShowVehiclesComponent::class)->name('show');
        Route::get('/{vehicle}/edit', \App\Livewire\Vehicles\EditVehiclesComponent::class)->name('edit')
            ->middleware('can:edit vehicles');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->middleware('can:view invoices')->group(function () {
        Route::get('/', \App\Livewire\Invoices\InvoicesComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Invoices\CreateInvoicesComponent::class)->name('create')
            ->middleware('can:create invoices');
        Route::get('/{invoice}', \App\Livewire\Invoices\ShowInvoicesComponent::class)->name('show');
        // Route::get('/{invoice}/edit', \App\Livewire\Invoices\EditInvoicesComponent::class)->name('edit')
        //     ->middleware('can:edit invoices');
    });

    // // Payments
    Route::prefix('payments')->name('payments.')->middleware('can:view payments')->group(function () {
        Route::get('/', \App\Livewire\Payments\PaymentsComponent::class)->name('index');
    });

    // // Expenses
    Route::prefix('expenses')->name('expenses.')->middleware('can:view expenses')->group(function () {
        Route::get('/', \App\Livewire\Expenses\ExpensesComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Expenses\CreateExpensesComponent::class)->name('create')
            ->middleware('can:create expenses');
    });

    // // Commissions
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', \App\Livewire\Commissions\CommissionsComponent::class)->name('index');
    });

    // // Appointments
    Route::prefix('appointments')->name('appointments.')->middleware('can:view appointments')->group(function () {
        Route::get('/', \App\Livewire\Appointments\AppointmentsComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Appointments\CreateAppointmentsComponent::class)->name('create')
            ->middleware('can:create appointments');
    });

    // // Calendar
    Route::get('/calendar', \App\Livewire\Calendar\CalendarComponent::class)
        ->name('calendar');

    // // Bay Status
    Route::get('/bays', \App\Livewire\Bays\BayStatusComponent::class)->name('bays.status');

    // // Inventory
    Route::prefix('inventory')->name('inventory.')->middleware('can:view inventory')->group(function () {
        Route::get('/', \App\Livewire\Inventory\InventoryComponent::class)->name('index');
        Route::get('/low-stock', \App\Livewire\Inventory\LowStock::class)->name('low-stock');
        Route::get('/movements', \App\Livewire\Inventory\Movements::class)->name('movements')
            ->middleware('can:adjust stock');
        Route::get('/create', \App\Livewire\Inventory\CreateInventoryComponent::class)->name('create')
            ->middleware('can:create inventory');
        Route::get('/{inventoryItem}', \App\Livewire\Inventory\ShowInventoryComponent::class)->name('show');
        Route::get('/{inventoryItem}/edit', \App\Livewire\Inventory\EditInventoryComponent::class)->name('edit')
            ->middleware('can:edit inventory');
    });

    // // Suppliers
    Route::prefix('suppliers')->name('suppliers.')->middleware('can:view suppliers')->group(function () {
        Route::get('/', \App\Livewire\Suppliers\SuppliersComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Suppliers\CreateSuppliersComponent::class)->name('create')
            ->middleware('can:create suppliers');
        Route::get('/{supplier}', \App\Livewire\Suppliers\ShowSuppliersComponent::class)->name('show');
    });

    // // Staff / Users
    Route::prefix('users')->name('users.')->middleware('can:view users')->group(function () {
        Route::get('/', \App\Livewire\Users\UsersComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Users\CreateUsersComponent::class)->name('create')
            ->middleware('can:create users');
        Route::get('/{user}', \App\Livewire\Users\ShowUsersComponent::class)->name('show');
        Route::get('/{user}/edit', \App\Livewire\Users\EditUsersComponent::class)->name('edit')
            ->middleware('can:edit users');
    });

    // // Branches
    Route::prefix('branches')->name('branches.')->middleware('can:view branches')->group(function () {
        Route::get('/', \App\Livewire\Branches\BranchesComponent::class)->name('index');
        Route::get('/create', \App\Livewire\Branches\CreateBranchesComponent::class)->name('create')
            ->middleware('can:create branches');
        Route::get('/{branch}/edit', \App\Livewire\Branches\EditBranchesComponent::class)->name('edit')
            ->middleware('can:edit branches');
    });

    // // Templates (Service Templates & Wash Packages)
    Route::prefix('templates')->name('templates.')->middleware('can:view service templates')->group(function () {
        Route::get('/', \App\Livewire\Templates\TemplatesComponent::class)->name('index');
    });

    // // Packages (Wash Packages)
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', \App\Livewire\Packages\PackagesComponent::class)->name('index');
    });

    // // Reports
    Route::prefix('reports')->name('reports.')->middleware('can:view reports')->group(function () {
        Route::get('/sales', \App\Livewire\Reports\Sales::class)->name('sales');
        Route::get('/operations', \App\Livewire\Reports\Operations::class)->name('operations');
        Route::get('/inventory', \App\Livewire\Reports\Inventory::class)->name('inventory');
        Route::get('/staff', \App\Livewire\Reports\Staff::class)->name('staff');
    });

    // // Platform Admin Routes
    Route::prefix('platform')->name('platform.')->group(function () {
        Route::get('/vendors', \App\Livewire\Platform\Vendors\Index::class)->name('vendors.index');
        Route::get('/vendors/create', \App\Livewire\Platform\Vendors\Create::class)->name('vendors.create');
        Route::get('/vendors/{vendor}', \App\Livewire\Platform\Vendors\Show::class)->name('vendors.show');
        Route::get('/billing', \App\Livewire\Platform\Billing::class)->name('billing');
    });

    // new vendor -platform -pricing
    Route::get('/platform-pricing', \App\Livewire\Platform\PlansComponent::class)->name('platform.pricing');

});
