<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // THE SPINE. Platform-owned canonical product. NOT vendor scoped.
    // Both marketplace_listings and garage inventory_items reference this.
    public function up(): void
    {
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->nullable();              // manufacturer brand, e.g. "Bosch"
            $table->string('part_number')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained('part_categories')->nullOnDelete();
            $table->string('unit_of_measure', 20)->default('unit'); // unit | litre | set | pair | metre
            $table->string('image')->nullable();
            $table->text('description')->nullable();

            // Provenance: which vendor proposed it (suppliers may submit unverified products).
            $table->foreignId('created_by_vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->boolean('is_verified')->default(false)->index(); // platform-admin moderated
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->unique(['brand', 'part_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_products');
    }
};
