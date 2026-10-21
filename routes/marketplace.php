<?php

use App\Domains\Marketplace\Livewire\Buyer\Browse;
use App\Domains\Marketplace\Livewire\Buyer\ListingDetail;
use App\Domains\Marketplace\Livewire\Buyer\PurchaseOrders\Index as BuyerPoIndex;
use App\Domains\Marketplace\Livewire\Buyer\PurchaseOrders\Receive as BuyerPoReceive;
use App\Domains\Marketplace\Livewire\Buyer\Quotes\Compare as QuotesCompare;
use App\Domains\Marketplace\Livewire\Buyer\Rfq\Create as RfqCreate;
use App\Domains\Marketplace\Livewire\Buyer\Rfq\Index as RfqIndex;
use App\Domains\Marketplace\Livewire\Storefront\Index as StorefrontIndex;
use App\Domains\Marketplace\Livewire\Storefront\Suppliers\Index as StorefrontSuppliersIndex;
use App\Domains\Marketplace\Livewire\Storefront\Suppliers\Show as StorefrontSuppliersShow;
use App\Domains\Marketplace\Livewire\Supplier\Catalog\Create as SupplierCatalogCreate;
use App\Domains\Marketplace\Livewire\Supplier\Catalog\Index as SupplierCatalogIndex;
use App\Domains\Marketplace\Livewire\Supplier\Dashboard as SupplierDashboard;
use App\Domains\Marketplace\Livewire\Supplier\Listings\Index as SupplierListings;
use App\Domains\Marketplace\Livewire\Supplier\Orders\Fulfill as SupplierOrderFulfill;
use App\Domains\Marketplace\Livewire\Supplier\Orders\Index as SupplierOrders;
use App\Domains\Marketplace\Livewire\Supplier\Quotes\Compose as SupplierQuoteCompose;
use App\Domains\Marketplace\Livewire\Supplier\Quotes\Inbox as SupplierQuoteInbox;
use App\Domains\Marketplace\Livewire\Supplier\Registration\Register as SupplierRegister;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketplace routes
|--------------------------------------------------------------------------
| Loaded by MarketplaceServiceProvider::boot(). Assumes the app's standard
| web middleware group + 'auth'. Permission middleware uses Spatie's
| 'permission:' alias (registered by spatie/laravel-permission).
|
| If your project does not register the 'permission' alias, add to
| bootstrap/app.php -> withMiddleware():
|   $middleware->alias([
|       'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
|       'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
|   ]);
*/

/*
| This file is loaded via MarketplaceServiceProvider::loadRoutesFrom(), NOT through
| bootstrap/app.php's withRouting(web: ...) — so unlike routes/web.php, nothing here gets
| the 'web' middleware group (session, cookies, CSRF) automatically. Every group below must
| declare 'web' explicitly, or forms silently fail with no session/CSRF token to submit against.
*/

Route::middleware('web')->group(function () {
    Route::prefix('storefront')->name('storefront.')->group(function () {
        Route::get('/', StorefrontIndex::class)
            ->name('index');

        Route::get('/suppliers', StorefrontSuppliersIndex::class)
            ->name('suppliers.index');

        Route::get('/suppliers/{vendor}', StorefrontSuppliersShow::class)
            ->name('suppliers.show');
    });

    Route::middleware('guest')->group(function () {
        Route::get('/become-a-supplier', SupplierRegister::class)
            ->name('supplier.register');
    });
});

Route::middleware(['web', 'auth'])->group(function () {

    /* ---------------- Supplier side ---------------- */
    Route::prefix('supplier')->name('supplier.')->group(function () {
        Route::get('/dashboard', SupplierDashboard::class)
            ->middleware('permission:view_quotes|view_listings')
            ->name('dashboard');

        Route::get('/listings', SupplierListings::class)
            ->middleware('permission:view_listings')
            ->name('listings.index');

        Route::get('/catalog', SupplierCatalogIndex::class)
            ->middleware('permission:view_catalog')
            ->name('catalog.index');

        Route::get('/catalog/create', SupplierCatalogCreate::class)
            ->middleware('permission:manage_catalog')
            ->name('catalog.create');

        Route::get('/quotes/inbox', SupplierQuoteInbox::class)
            ->middleware('permission:view_quotes')
            ->name('quotes.inbox');

        Route::get('/quotes/compose/{rfq}', SupplierQuoteCompose::class)
            ->middleware('permission:manage_quotes')
            ->name('quotes.compose');

        Route::get('/orders', SupplierOrders::class)
            ->middleware('permission:view_purchase_orders')
            ->name('orders.index');

        Route::get('/orders/{purchaseOrder}/fulfill', SupplierOrderFulfill::class)
            ->middleware('permission:manage_purchase_orders')
            ->name('orders.fulfill');
    });

    /* ---------------- Buyer / marketplace side ---------------- */
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/', Browse::class)
            ->middleware('permission:browse_marketplace')
            ->name('browse');

        Route::get('/listings/{listing}', ListingDetail::class)
            ->middleware('permission:browse_marketplace')
            ->name('listings.show');

        Route::get('/rfqs', RfqIndex::class)
            ->middleware('permission:view_rfqs')
            ->name('rfqs.index');

        Route::get('/rfqs/create', RfqCreate::class)
            ->middleware('permission:manage_rfqs')
            ->name('rfqs.create');

        Route::get('/rfqs/{rfq}/quotes', QuotesCompare::class)
            ->middleware('permission:award_quotes')
            ->name('quotes.compare');

        Route::get('/purchase-orders', BuyerPoIndex::class)
            ->middleware('permission:view_purchase_orders')
            ->name('purchase-orders.index');

        Route::get('/purchase-orders/{purchaseOrder}/receive', BuyerPoReceive::class)
            ->middleware('permission:receive_goods')
            ->name('purchase-orders.receive');
    });
});
