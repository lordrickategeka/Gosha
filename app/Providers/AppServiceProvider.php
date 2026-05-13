<?php

namespace App\Providers;

use App\Models\WashBay;
use App\Models\WashOrder;
use App\Observers\WashOrderObserver;
use App\Policies\WashBayPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use App\Http\Livewire\Staff\CreateStaffComponent;
use App\Models\InventoryItem;
use App\Models\WorkOrder;
use App\Observers\InventoryItemObserver;
use App\Observers\WorkOrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        \Illuminate\Support\Facades\Blade::component('layouts.dash-layout', 'layouts.dash-layout');

        // Livewire::component('staff.create-staff-component', CreateStaffComponent::class);

        // Observers
        WashOrder::observe(WashOrderObserver::class);
        WorkOrder::observe(WorkOrderObserver::class);
        InventoryItem::observe(InventoryItemObserver::class);

        // Policies
        Gate::policy(WashBay::class, WashBayPolicy::class);

    }
}
