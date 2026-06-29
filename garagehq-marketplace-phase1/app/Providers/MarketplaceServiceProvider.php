<?php

namespace App\Providers;

use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Observers\GoodsReceiptItemObserver;
use App\Observers\PurchaseOrderObserver;
use Illuminate\Support\ServiceProvider;

/**
 * Register in bootstrap/providers.php (Laravel 11):
 *   App\Providers\MarketplaceServiceProvider::class,
 */
class MarketplaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/marketplace.php', 'marketplace');
    }

    public function boot(): void
    {
        GoodsReceiptItem::observe(GoodsReceiptItemObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);

        $this->loadRoutesFrom(__DIR__ . '/../../routes/marketplace.php');
    }
}
