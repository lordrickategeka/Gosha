# Marketplace Cross-Tenant Scoping

> Read this before touching any marketplace query. The boundary here is the
> opposite of the rest of GarageHQ, and getting it wrong fails silently.

## The conflict

Everywhere else in GarageHQ, models use `BelongsToVendor`, which applies a **global**
scope filtering every query by the current `vendor_id`. That is exactly right for a
single tenant's own data.

The marketplace is inherently **cross-tenant**: a *buyer* vendor transacts with a
*supplier* vendor. If a marketplace model carried the global vendor scope:

- A garage browsing listings would only ever see *its own* listings (almost always none).
- A supplier viewing an incoming PO whose `buyer_vendor_id` is a different tenant would
  get an **empty result set** — no error, just nothing. Silent and very hard to debug.

So marketplace models **do NOT use `BelongsToVendor`.**

## The rules

1. **Named keys, never `vendor_id`.** Marketplace tables use explicit, role-named columns:
   `buyer_vendor_id`, `supplier_vendor_id`. There is deliberately no `vendor_id` column on
   these tables, so the global scope has nothing to latch onto even by accident.

2. **`ScopedToMarketplaceParticipant` trait** provides the local scopes you filter with:
   - `asBuyer()` — rows where the current vendor is the buyer.
   - `asSupplier()` — rows where the current vendor is the supplier.
   - `forCurrentParticipant()` — rows where the current vendor is on *either* side
     (uses the model's `$participantColumns`). Safe default for "things I'm involved in".

   Current vendor resolves from `session('current_vendor_id')`, falling back to the
   authenticated user's `vendor_id`.

3. **Listings have two explicit views** (on `MarketplaceListing`):
   - `browsable()` — every active, in-stock listing, **ownership ignored**. The buyer view.
   - `ownedBySupplier()` — only the current vendor's own listings. The management view.

   These are separate scopes precisely so the cross-tenant read (`browsable`) and the
   owned-data read (`ownedBySupplier`) can never be confused.

4. **Always scope explicitly.** Because there is no global safety net here, every
   marketplace query must choose its scope deliberately. A bare
   `PurchaseOrder::all()` returns *all tenants' POs* — never do that outside platform admin.

## Ownership enforcement in components

Detail/action screens re-assert ownership rather than trusting the route binding:

```php
// Compare.php — a vendor may only compare quotes on their OWN rfq
$this->rfq = Rfq::asBuyer()->whereKey($rfq->id)->firstOrFail();

// Receive.php — only the buyer may receive against a PO
$this->purchaseOrder = PurchaseOrder::asBuyer()->whereKey($po->id)->firstOrFail();
```

`firstOrFail()` on the scoped query turns "not mine" into a 404, which is the behaviour
you want.

## Participant columns per model

| Model                  | `$participantColumns`                     |
|------------------------|-------------------------------------------|
| `Rfq`                  | `['buyer_vendor_id']`                     |
| `Quote`                | `['supplier_vendor_id']`                  |
| `PurchaseOrder`        | `['buyer_vendor_id','supplier_vendor_id']`|
| `MarketplaceTransaction` | `['buyer_vendor_id','supplier_vendor_id']`|

`MarketplaceListing` uses its own `browsable()` / `ownedBySupplier()` scopes rather than
the participant trait, because its two reads are asymmetric (one ignores ownership entirely).

## Catalog is platform-owned, not scoped at all

`catalog_products`, `part_categories`, `vehicle_*`, and `part_vehicle_compatibilities`
have **no tenant column**. They are shared reference data owned by the platform. Suppliers
may *propose* products (`is_verified = false`, with `created_by_vendor_id` for provenance);
a platform admin verifies them. Never add a vendor scope to these tables.
