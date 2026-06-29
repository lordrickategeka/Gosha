<?php

use App\Livewire\Marketplace\Browse;
use App\Livewire\Marketplace\ListingDetail;
use App\Livewire\Marketplace\PurchaseOrders\Index as BuyerPoIndex;
use App\Livewire\Marketplace\PurchaseOrders\Receive as BuyerPoReceive;
use App\Livewire\Marketplace\Quotes\Compare as QuotesCompare;
use App\Livewire\Marketplace\Rfq\Create as RfqCreate;
use App\Livewire\Marketplace\Rfq\Index as RfqIndex;
use App\Livewire\Supplier\Dashboard as SupplierDashboard;
use App\Livewire\Supplier\Listings\Index as SupplierListings;
use App\Livewire\Supplier\Orders\Fulfill as SupplierOrderFulfill;
use App\Livewire\Supplier\Orders\Index as SupplierOrders;
use App\Livewire\Supplier\Quotes\Compose as SupplierQuoteCompose;
use App\Livewire\Supplier\Quotes\Inbox as SupplierQuoteInbox;
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

Route::middleware(['web', 'auth'])->group(function () {

    /* ---------------- Supplier side ---------------- */
    Route::prefix('supplier')->name('supplier.')->group(function () {
        Route::get('/dashboard', SupplierDashboard::class)
            ->name('dashboard');

        Route::get('/listings', SupplierListings::class)
            ->middleware('permission:view_listings')
            ->name('listings.index');

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
