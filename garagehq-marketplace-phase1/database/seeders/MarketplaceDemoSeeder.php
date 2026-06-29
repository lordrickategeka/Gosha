<?php

namespace Database\Seeders;

use App\Models\CatalogProduct;
use App\Models\MarketplaceListing;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

/**
 * Optional demo: promotes/creates a supplier vendor and seeds listings (with one tiered price)
 * against the verified catalog. Run AFTER VehicleCatalogSeeder. Idempotent-ish: safe to re-run.
 *
 * Adjust the Vendor lookups to match your factory/seeded tenants.
 */
class MarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Promote an existing vendor to supplier, or create one if none exists.
        $supplier = Vendor::query()->where('vendor_type', 'supplier')->first()
            ?? Vendor::query()->first();

        if (! $supplier) {
            $this->command?->warn('No vendors found; skipping marketplace demo listings.');
            return;
        }

        $supplier->update([
            'vendor_type' => 'supplier',
            'is_verified_supplier' => true,
        ]);

        $currency = config('marketplace.default_currency', 'UGX');

        $priceBook = [
            'Oil Filter' => 28000,
            'Front Brake Pads' => 145000,
            'Spark Plug' => 18000,
            'Front Shock Absorber' => 210000,
            'Car Wash Shampoo 1.89L' => 65000,
        ];

        foreach (CatalogProduct::verified()->active()->get() as $product) {
            $price = $priceBook[$product->name] ?? 50000;

            $listing = MarketplaceListing::firstOrCreate(
                ['supplier_vendor_id' => $supplier->id, 'catalog_product_id' => $product->id],
                [
                    'supplier_sku' => 'SKU-' . $product->id,
                    'price' => $price,
                    'currency' => $currency,
                    'stock_qty' => 120,
                    'min_order_qty' => 1,
                    'lead_time_days' => 2,
                    'condition' => 'new',
                    'is_active' => true,
                ]
            );

            // One bulk tier as a demo of tiered pricing resolution.
            $listing->priceTiers()->firstOrCreate(
                ['min_qty' => 10],
                ['unit_price' => round($price * 0.9, 2)]
            );
        }
    }
}
