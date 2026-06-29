<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Pivot wiring catalog products to the vehicles they fit. Powers compatibility AND recommendations.
    public function up(): void
    {
        Schema::create('part_vehicle_compatibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_variant_id')->constrained()->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->unique(['catalog_product_id', 'vehicle_variant_id'], 'cat_prod_veh_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_vehicle_compatibilities');
    }
};
