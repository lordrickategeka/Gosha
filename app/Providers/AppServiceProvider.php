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

        // Policies
        Gate::policy(WashBay::class, WashBayPolicy::class);
    }
}
