<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('unit')->default('pieces'); // pieces, liters, kg, etc.
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('reorder_level', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->string('location')->nullable(); // shelf/bin location
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'sku']);
            $table->index(['vendor_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
