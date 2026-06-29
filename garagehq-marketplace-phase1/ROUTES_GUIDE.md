# Routes Guide — Marketplace Module

`routes/marketplace.php` is loaded automatically by `MarketplaceServiceProvider::boot()`
via `loadRoutesFrom()`. You do not need to require it in `web.php`.

## Route map

### Supplier (`supplier.` prefix, sellers)

| Name                       | URI                                    | Component                         | Permission                  |
|----------------------------|----------------------------------------|-----------------------------------|-----------------------------|
| `supplier.dashboard`       | `/supplier/dashboard`                  | `Supplier\Dashboard`              | auth                        |
| `supplier.listings.index`  | `/supplier/listings`                   | `Supplier\Listings\Index`         | `view_listings`             |
| `supplier.quotes.inbox`    | `/supplier/quotes/inbox`               | `Supplier\Quotes\Inbox`           | `view_quotes`               |
| `supplier.quotes.compose`  | `/supplier/quotes/compose/{rfq}`       | `Supplier\Quotes\Compose` *(stub)*| `manage_quotes`             |
| `supplier.orders.index`    | `/supplier/orders`                     | `Supplier\Orders\Index`           | `view_purchase_orders`      |
| `supplier.orders.fulfill`  | `/supplier/orders/{purchaseOrder}/fulfill` | `Supplier\Orders\Fulfill` *(stub)* | `manage_purchase_orders` |

### Buyer / marketplace (`marketplace.` prefix, garages & car washes)

| Name                                | URI                                          | Component                              | Permission                |
|-------------------------------------|----------------------------------------------|----------------------------------------|---------------------------|
| `marketplace.browse`                | `/marketplace`                               | `Marketplace\Browse`                   | `browse_marketplace`      |
| `marketplace.listings.show`         | `/marketplace/listings/{listing}`            | `Marketplace\ListingDetail` *(stub)*   | `browse_marketplace`      |
| `marketplace.rfqs.index`            | `/marketplace/rfqs`                           | `Marketplace\Rfq\Index`                | `view_rfqs`               |
| `marketplace.rfqs.create`           | `/marketplace/rfqs/create`                    | `Marketplace\Rfq\Create`               | `manage_rfqs`             |
| `marketplace.quotes.compare`        | `/marketplace/rfqs/{rfq}/quotes`              | `Marketplace\Quotes\Compare`           | `award_quotes`            |
| `marketplace.purchase-orders.index` | `/marketplace/purchase-orders`                | `Marketplace\PurchaseOrders\Index`     | `view_purchase_orders`    |
| `marketplace.purchase-orders.receive` | `/marketplace/purchase-orders/{purchaseOrder}/receive` | `Marketplace\PurchaseOrders\Receive` | `receive_goods`     |

## Spatie middleware alias

The routes use the `permission:` middleware alias. If your app hasn't registered it,
add to `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
    ]);
})
```

## The reorder deep link

Wire your existing low-stock notification's action button to:

```php
route('marketplace.browse', ['catalog_product_id' => $inventoryItem->catalog_product_id])
```

`Browse` reads `?catalog_product_id=` and pre-filters to suppliers of that exact product,
closing the loop: low stock → reorder → buy/RFQ → PO → goods receipt → stock back in.
(Requires the inventory item to be linked to a catalog product — see README.)
