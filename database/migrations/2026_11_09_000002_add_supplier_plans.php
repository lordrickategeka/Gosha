<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->string('audience')->default('garage')->index();
            $table->unsignedInteger('max_active_listings')->nullable();
        });
        Schema::table('marketplace_transactions', function (Blueprint $table) {
            $table->foreignId('platform_invoice_id')->nullable()->constrained('vendor_platform_invoices')->nullOnDelete();
        });
        foreach ([
            ['supplier-trial', 'Supplier free trial', 0, 50, 2, 0, true],
            ['supplier-starter', 'Supplier Starter', 30000, 100, 2, 5, false],
            ['supplier-growth', 'Supplier Growth', 75000, 500, 5, 3, false],
            ['supplier-pro', 'Supplier Pro', 150000, 2000, 15, 2, false],
        ] as $position => [$slug, $name, $price, $listings, $users, $rate, $trial]) {
            DB::table('pricing_plans')->insert([
                'slug' => $slug, 'name' => $name, 'audience' => 'supplier',
                'description' => $trial ? '14 days of selling access after platform approval. No card or automatic charge.' : 'Catalog, stock, quotations and order management for your supplier business.',
                'billing_model' => $trial ? 'free' : 'hybrid', 'base_price' => $price,
                'currency' => 'UGX', 'billing_cycle' => 'monthly', 'commission_rate' => $rate,
                'commission_base' => 'gross_revenue', 'max_active_listings' => $listings, 'max_users' => $users,
                'is_trial' => $trial, 'has_trial' => $trial, 'trial_days' => $trial ? 14 : 0,
                'trial_requires_card' => false, 'setup_fee' => 0, 'is_active' => true,
                'is_featured' => $slug === 'supplier-growth', 'sort_order' => $position,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('marketplace_transactions', fn (Blueprint $table) => $table->dropConstrainedForeignId('platform_invoice_id'));
        Schema::table('pricing_plans', fn (Blueprint $table) => $table->dropColumn(['audience', 'max_active_listings']));
    }
};
