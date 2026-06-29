<?php

namespace App\Providers;

use App\Domains\Operations\Models\WashBay;
use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Observers\WashOrderObserver;
use App\Domains\Operations\Policies\WashBayPolicy;
use App\Domains\Organization\Models\AuditLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use App\Domains\Inventory\Models\InventoryItem;
use App\Domains\Operations\Models\WorkOrder;
use App\Domains\Inventory\Observers\InventoryItemObserver;
use App\Domains\Operations\Observers\WorkOrderObserver;

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

        // Inline Livewire components used via <livewire:...> blade tags
        Livewire::component('customer-vehicle-selector', \App\Domains\CRM\Livewire\CustomerVehicleSelector::class);
        Livewire::component('settings.taxonomy-component', \App\Domains\Organization\Livewire\Settings\TaxonomyComponent::class);
        Livewire::component('work-orders.parts-intelligence-panel', \App\Domains\Operations\Livewire\WorkOrders\PartsIntelligencePanel::class);
        Livewire::component('notifications.bell', \App\Domains\Notifications\Livewire\NotificationBell::class);
        Livewire::component('notifications.center', \App\Domains\Notifications\Livewire\NotificationCenter::class);

        // Resolve Livewire domain components that live outside the configured App\Livewire namespace.
        // Without this, AJAX update requests fail because generateClassFromName() prepends App\Livewire.
        Livewire::resolveMissingComponent(function (string $name): ?string {
            $class = collect(str($name)->explode('.'))
                ->map(fn ($segment) => (string) str($segment)->studly())
                ->join('\\');

            if (class_exists($class) && is_subclass_of($class, \Livewire\Component::class)) {
                return $class;
            }

            return null;
        });

        // Observers
        WashOrder::observe(WashOrderObserver::class);
        WorkOrder::observe(WorkOrderObserver::class);
        InventoryItem::observe(InventoryItemObserver::class);

        // Policies
        Gate::policy(WashBay::class, WashBayPolicy::class);

        // Post-login hook: set branch session + audit log (fires for both Livewire and Fortify logins)
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            $user = $event->user;
            if (!session('current_branch_id')) {
                $branch = $user->primaryBranch() ?? ($user->branches->first() ?? null);
                if ($branch) {
                    session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name]);
                }
            }
            try { AuditLog::logLogin($user); } catch (\Throwable) {}
        });
    }
}
