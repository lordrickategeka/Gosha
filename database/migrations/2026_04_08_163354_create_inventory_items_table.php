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
            $table->enum('item_type', ['service_part', 'wash_supply', 'consumable', 'tool'])->default('service_part');
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


            // Service parts specific
            $table->string('oem_number')->nullable();
            $table->string('aftermarket_number')->nullable();
            $table->enum('position', [
                'front',
                'rear',
                'left',
                'right',
                'front_left',
                'front_right',
                'rear_left',
                'rear_right',
                'upper',
                'lower',
                'inner',
                'outer',
                'center',
                'universal'
            ])->nullable();
            $table->enum('condition', ['new', 'used', 'refurbished', 'reconditioned'])
                ->default('new');

            // Wash supplies specific
            $table->enum('unit_of_measure', ['pcs', 'liters', 'ml', 'kg', 'g', 'meters', 'cm'])
                ->default('pcs');
            $table->date('expiry_date')->nullable();
            $table->decimal('usage_rate', 8, 2)->nullable();

            // Common enhancements
            $table->string('brand')->nullable(); // Bosch, Denso, etc.
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();

            $table->timestamps();


            // Indexes for search performance
            $table->index(['is_active']);
            $table->index(['oem_number']);
            $table->index(['aftermarket_number']);
            $table->index(['barcode']);

            $table->index(['vendor_id', 'sku']);
            $table->index(['vendor_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
