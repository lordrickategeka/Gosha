<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Customer\CustomersComponent;
use App\Livewire\JobCards\JobCardsComponent;
use App\Livewire\Vehicles\VehiclesComponent;

use App\Livewire\Staff\StaffComponent;
use App\Livewire\ServiceTypesComponent;
use App\Livewire\JobCards\CreateJobCardComponent;
use App\Livewire\Workshop\WorkshopJobcardCreate;
use App\Livewire\Workshop\WorkshopJobcardIndex;

// Inventory Module Routes with Livewire
use App\Livewire\Dashboard\DashboardComponent;
use App\Livewire\InventoryList;
use App\Livewire\InventoryForm;
use App\Livewire\Vehicles\AllVehicles;
use App\Livewire\Vehicles\VehicleDetails;
use App\Livewire\Vehicles\VehicleTypesComponent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardComponent::class)->name('dashboard');

    // Job Cards
    // Route::patch('job-cards/{jobCard}/status', [JobCardController::class, 'updateStatus'])->name('job-cards.update-status');
    Route::get('/customers', CustomersComponent::class)->name('customers');

    Route::get('/staff', StaffComponent::class)->name('staff.index');
    Route::get('/job-cards', JobCardsComponent::class)->name('job-cards.index');

    // Job Card Form
    Route::get('/job-cards/create', CreateJobCardComponent::class)->name('job-cards.create');
    Route::get('/job-cards/{jobCard}/edit', CreateJobCardComponent::class)->name('job-cards.edit');

    // Workshop Jobcards
    Route::get('/workshop-jobcards/create/{jobCard?}', WorkshopJobcardCreate::class)->name('workshop-jobcards.create');
    Route::get('/workshop-jobcards/{workshopJobcard}', \App\Livewire\Workshop\WorkshopJobcardShow::class)->name('workshop-jobcards.show');
    Route::get('/workshop-jobcards', \App\Livewire\Workshop\WorkshopJobcardIndex::class)->name('workshop-jobcards.index');

    Route::get('/service-types', ServiceTypesComponent::class)->name('service-types');

    //vehicle
    Route::get('/all-vehicles', AllVehicles::class)->name('vehicles.all');


    // Payments
    // Route::get('invoices/{invoice}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    // Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');

    // Invoices
    // Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    // Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    // Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    // Expenses
    // Route::resource('expenses', ExpenseController::class);

    // Reports
    // Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    // Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    // Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    // Route::get('/reports/commissions', [ReportController::class, 'commissions'])->name('reports.commissions');

    // Staff Management
    // Route::resource('staff', StaffController::class);

    // Settings
    // Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    // Route::resource('vehicle-types', VehicleTypeController::class);

    // Vehicle Types (Livewire)
    Route::get('/vehicle-types', VehicleTypesComponent::class)->name('vehicle-types');

    // Route::resource('service-types', ServiceTypeController::class);
    // Route::resource('commission-price-lists', CommissionPriceListController::class);
    Route::get('/customers', CustomersComponent::class)->name('customers');


    Route::prefix('inventory')->group(function () {
        Route::get('/', InventoryList::class)->name('inventory.index');
        Route::get('/create', InventoryForm::class)->name('inventory.create');
        Route::get('/{inventoryItem}/edit', InventoryForm::class)->name('inventory.edit');
    });

    Route::get('/vehicles/{vehicleId}', VehicleDetails::class)->name('vehicles.show');

    Route::get('/staff/create', \App\Livewire\Staff\CreateStaffComponent::class)->name('staff.create');

});

require __DIR__ . '/auth.php';
