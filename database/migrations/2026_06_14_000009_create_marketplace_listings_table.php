<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // A supplier's offer of a catalog product. Cross-tenant: NOT under the global vendor scope.
    public function up(): void
    {
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            // Named key (NOT vendor_id) so the global BelongsToVendor scope is never applied here.
            $table->foreignId('supplier_vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('catalog_product_id')->constrained()->cascadeOnDelete();
            $table->string('supplier_sku')->nullable();
            $table->decimal('price', 14, 2);
            $table->string('currency', 3)->default('UGX');
            $table->integer('stock_qty')->default(0);
            $table->unsignedInteger('min_order_qty')->default(1);
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->string('condition', 20)->default('new'); // new | used | refurbished
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['supplier_vendor_id', 'is_active']);
            $table->index(['catalog_product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_listings');
    }
};
