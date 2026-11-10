<?php

namespace App\Providers;

use App\Domains\Marketplace\Models\GoodsReceiptItem;
use App\Domains\Marketplace\Models\PurchaseOrder;
use App\Domains\Marketplace\Observers\GoodsReceiptItemObserver;
use App\Domains\Marketplace\Observers\PurchaseOrderObserver;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

/**
 * Register in bootstrap/providers.php:
 *   App\Providers\MarketplaceServiceProvider::class,
 */
class MarketplaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/marketplace.php', 'marketplace');
    }

    public function boot(): void
    {
        Livewire::addPersistentMiddleware([\App\Http\Middleware\EnsureSupplierWorkspace::class]);
        // Register observers
        GoodsReceiptItem::observe(GoodsReceiptItemObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/marketplace.php');

        // Register Livewire components for the Marketplace namespace
        Livewire::component('marketplace.buyer.browse', \App\Domains\Marketplace\Livewire\Buyer\Browse::class);
        Livewire::component('marketplace.buyer.listing-detail', \App\Domains\Marketplace\Livewire\Buyer\ListingDetail::class);
        Livewire::component('marketplace.buyer.purchase-orders.index', \App\Domains\Marketplace\Livewire\Buyer\PurchaseOrders\Index::class);
        Livewire::component('marketplace.buyer.purchase-orders.receive', \App\Domains\Marketplace\Livewire\Buyer\PurchaseOrders\Receive::class);
        Livewire::component('marketplace.buyer.quotes.compare', \App\Domains\Marketplace\Livewire\Buyer\Quotes\Compare::class);
        Livewire::component('marketplace.buyer.rfq.index', \App\Domains\Marketplace\Livewire\Buyer\Rfq\Index::class);
        Livewire::component('marketplace.buyer.rfq.create', \App\Domains\Marketplace\Livewire\Buyer\Rfq\Create::class);
        Livewire::component('marketplace.supplier.dashboard', \App\Domains\Marketplace\Livewire\Supplier\Dashboard::class);
        Livewire::component('marketplace.supplier.enable-module', \App\Domains\Marketplace\Livewire\Supplier\EnableModule::class);
        Livewire::component('marketplace.supplier.listings.index', \App\Domains\Marketplace\Livewire\Supplier\Listings\Index::class);
        Livewire::component('marketplace.supplier.catalog.index', \App\Domains\Marketplace\Livewire\Supplier\Catalog\Index::class);
        Livewire::component('marketplace.supplier.catalog.create', \App\Domains\Marketplace\Livewire\Supplier\Catalog\Create::class);
        Livewire::component('marketplace.platform.catalog.index', \App\Domains\Marketplace\Livewire\Platform\Catalog\Index::class);
        Livewire::component('marketplace.supplier.orders.index', \App\Domains\Marketplace\Livewire\Supplier\Orders\Index::class);
        Livewire::component('marketplace.supplier.orders.fulfill', \App\Domains\Marketplace\Livewire\Supplier\Orders\Fulfill::class);
        Livewire::component('marketplace.supplier.quotes.inbox', \App\Domains\Marketplace\Livewire\Supplier\Quotes\Inbox::class);
        Livewire::component('marketplace.supplier.quotes.compose', \App\Domains\Marketplace\Livewire\Supplier\Quotes\Compose::class);

        // Public storefront (no auth required)
        Livewire::component('marketplace.storefront.index', \App\Domains\Marketplace\Livewire\Storefront\Index::class);
        Livewire::component('marketplace.storefront.suppliers.index', \App\Domains\Marketplace\Livewire\Storefront\Suppliers\Index::class);
        Livewire::component('marketplace.storefront.suppliers.show', \App\Domains\Marketplace\Livewire\Storefront\Suppliers\Show::class);
        Livewire::component('marketplace.supplier.registration.register', \App\Domains\Marketplace\Livewire\Supplier\Registration\Register::class);
    }
}
