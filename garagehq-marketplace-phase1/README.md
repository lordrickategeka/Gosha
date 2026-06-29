# GarageHQ Marketplace — Phase 1

A multi-vendor B2B marketplace bolted onto GarageHQ: supplier shops sell inventory to
garages and car washes through listings, RFQs/quotes, and purchase orders, with goods
receipt that feeds straight back into existing garage stock. Ships the canonical product
catalog spine + vehicle-compatibility system as its foundation, and meters platform
commission from day one.

## What's in this package

```
database/migrations/   20 migrations (vendor_type, catalog spine, compatibility,
                        listings + tiers, RFQ/quote, PO/goods-receipt, commission)
database/seeders/      3 seeders (roles+perms, canonical catalog, demo listings)
app/Enums/             5 enums (RfqStatus, QuoteStatus, PurchaseOrderStatus,
                        PaymentStatus, VendorType)
app/Traits/            ScopedToMarketplaceParticipant  (the cross-tenant keystone)
app/Models/            18 models
app/Services/          RfqService, PurchaseOrderService, MarketplaceCommissionService
app/Observers/         GoodsReceiptItemObserver (stock-in), PurchaseOrderObserver (commission)
app/Providers/         MarketplaceServiceProvider
app/Livewire/          13 components (supplier side + buyer/marketplace side)
resources/views/       13 matching Blade views (DaisyUI)
config/marketplace.php
routes/marketplace.php
MARKETPLACE_SCOPING.md  <-- read this; cross-tenant boundary is the opposite of the rest of the app
ROUTES_GUIDE.md
```

## Installation

1. **Copy files** into your app, preserving the directory structure (`app/...`,
   `database/...`, `resources/...`, `config/...`, `routes/...`).

2. **Register the provider** in `bootstrap/providers.php`:
   ```php
   App\Providers\MarketplaceServiceProvider::class,
   ```

3. **Migrate**:
   ```bash
   php artisan migrate
   ```

4. **Seed** (order matters):
   ```bash
   php artisan db:seed --class=Database\\Seeders\\MarketplaceRolesSeeder
   php artisan db:seed --class=Database\\Seeders\\VehicleCatalogSeeder
   php artisan db:seed --class=Database\\Seeders\\MarketplaceDemoSeeder   # optional demo data
   ```

5. **Spatie permission alias** — if not already present, register `permission` /
   `role` middleware aliases (see ROUTES_GUIDE.md).

## Assumptions made (verify against your codebase)

These are the points most likely to need a small adjustment to match your actual schema:

- **Vendor model** is `App\Models\Vendor` and the `users` table has a `vendor_id` column.
  If the namespace differs, update the `belongsTo(\App\Models\Vendor::class, ...)` references
  in the models (Browse uses it in a subquery too).
- **Existing roles** are named `garage_admin`, `branch_manager`, and `platform_admin`.
  `MarketplaceRolesSeeder` attaches buyer permissions to the first two and admin
  permissions to the third. Rename in the seeder if yours differ.
- **`inventory_items` table** has `vendor_id`, `branch_id`, a `type` discriminator, and a
  **quantity column named `quantity`**. The stock-in observer
  (`GoodsReceiptItemObserver`) writes to a constant `QTY_COLUMN = 'quantity'` — change it
  there if your column is `stock` / `qty_on_hand` / etc. Migration 08 adds a
  `catalog_product_id` FK to this table.
- **`part_categories` table** is created by migration 02 (memory indicated it wasn't built
  yet). If you already have an inventory category table you'd rather use, skip migration 02
  and repoint `catalog_products.category_id` (migration 06) at it.
- **`HasAuditLog` trait** exists at `App\Traits\HasAuditLog` and is used by `PurchaseOrder`.
- **`session('current_vendor_id')`** — the scoping trait reads this, falling back to
  `auth()->user()->vendor_id`. If you don't set a current-vendor session key, the fallback
  covers single-vendor-per-user setups automatically.
- **`branches` table** exists (FKs on rfqs / purchase_orders / goods_receipts are nullable,
  so this is non-fatal if branch context is optional).

## How the pieces connect (the payoff loop)

```
low-stock notification (existing)
   └─ "Reorder from marketplace"  → route('marketplace.browse', ['catalog_product_id' => ...])
        └─ Browse: buy a listing  OR  Rfq\Create → suppliers quote → Quotes\Compare → award
             └─ PurchaseOrderService creates a PO  (commission metered on Accept)
                  └─ PurchaseOrders\Receive creates GoodsReceiptItems
                       └─ GoodsReceiptItemObserver  →  inventory stock incremented ✔
```

## Commission metering

`PurchaseOrderObserver` calls `MarketplaceCommissionService::meter()` once, when a PO
transitions into `accepted`. It writes a `marketplace_transactions` row using the
supplier's plan rate where available, else `config('marketplace.default_commission_rate')`
(default 5%). Two `TODO(BillingService)` hooks mark exactly where to wire your existing
platform `BillingService` for rate resolution and invoicing — until then it degrades
gracefully and leaves transactions `pending`.

## Full vs stub components

**Fully implemented:** Supplier `Dashboard`, Supplier `Listings\Index` (CRUD),
Supplier `Quotes\Inbox`, Supplier `Orders\Index` (accept → commission),
Marketplace `Browse` (cross-tenant + reorder hook + direct buy),
Marketplace `Rfq\Create`, Marketplace `Rfq\Index`,
Marketplace `Quotes\Compare` (award → PO), Marketplace `PurchaseOrders\Index`,
Marketplace `PurchaseOrders\Receive` (goods receipt → stock-in).

**Stubbed** (route resolves, context loads, placeholder view; next implementation step):
Supplier `Quotes\Compose` (quote line-item builder), Supplier `Orders\Fulfill`
(dispatch/shipping view), Marketplace `ListingDetail` (compatibility + tier breakdown).

## Phase 2 (not in this package)

Recommendation engine (weighted compatibility + proximity + rating + price + reorder
history), ratings/reviews + verified-supplier KYC, supplier analytics, returns/disputes,
in-app messaging, and full `BillingService` invoicing of metered commission. Payment
gateway (MTN/Airtel/UGX) slots in behind the existing `purchase_orders.payment_status`.
