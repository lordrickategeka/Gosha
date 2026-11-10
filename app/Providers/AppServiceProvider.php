<?php

namespace App\Providers;

use App\Domains\Inventory\Models\InventoryItem;
use App\Domains\Inventory\Observers\InventoryItemObserver;
use App\Domains\Operations\Models\WashBay;
use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Models\WorkOrder;
use App\Domains\Operations\Observers\WashOrderObserver;
use App\Domains\Operations\Observers\WorkOrderObserver;
use App\Domains\Operations\Policies\WashBayPolicy;
use App\Domains\Organization\Models\AuditLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        \Illuminate\Support\Facades\RateLimiter::for('assistant', fn (\Illuminate\Http\Request $request) => [
            \Illuminate\Cache\RateLimiting\Limit::perMinute(6)->by('assistant:minute:'.$request->user()?->id),
            \Illuminate\Cache\RateLimiting\Limit::perDay(100)->by('assistant:day:'.$request->user()?->id),
            \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by('assistant:global'),
        ]);
        \App\Domains\Platform\Models\Vendor::observe(\App\Domains\Platform\Observers\SupplierApprovalObserver::class);
        Livewire::componentHook(\App\Livewire\SubscriptionAccessHook::class);
        foreach ([
            \App\Domains\Organization\Models\Branch::class => ['max_branches', false],
            \App\Models\User::class => ['max_users', false],
            \App\Domains\CRM\Models\Customer::class => ['max_customers', false],
            \App\Domains\Inventory\Models\InventoryItem::class => ['max_inventory_items', false],
            \App\Domains\Operations\Models\WorkOrder::class => ['max_work_orders_per_month', true],
            \App\Domains\Operations\Models\WashOrder::class => ['max_wash_orders_per_month', true],
        ] as $model => [$limit, $monthly]) {
            $model::creating(fn ($record) => app(\App\Domains\Platform\Services\SubscriptionCapacityGuard::class)->creating($record, $limit, $monthly));
        }
        // Local dev safety net: a stale `php artisan config:cache` run silently freezes
        // config values (e.g. DB_CONNECTION) even after .env is edited, causing confusing
        // "wrong driver/database" errors. Auto-clear it so the next request picks up .env.
        if ($this->app->environment('local') && $this->app->configurationIsCached()) {
            @unlink($this->app->getCachedConfigPath());
        }

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
            if (! session('current_branch_id')) {
                $branch = $user->primaryBranch() ?? ($user->branches->first() ?? null);
                if ($branch) {
                    session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name]);
                }
            }
            try {
                AuditLog::logLogin($user);
            } catch (\Throwable) {
            }
        });
    }
}
