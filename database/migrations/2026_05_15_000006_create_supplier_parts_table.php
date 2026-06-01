<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('part_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('part_oem_number_id')->nullable()->constrained('part_oem_numbers')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();

            $table->string('supplier_part_name', 255)->nullable();
            $table->string('supplier_part_number', 100)->nullable();
            $table->string('supplier_link', 2048)->nullable();

            $table->decimal('supplier_price', 14, 2)->nullable();
            $table->string('availability', 50)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->string('warranty_text', 255)->nullable();

            $table->decimal('shipping_cost', 14, 2)->nullable();
            $table->decimal('duty_cost', 14, 2)->nullable();
            $table->decimal('clearing_cost', 14, 2)->nullable();
            $table->decimal('margin_amount', 14, 2)->nullable();
            $table->decimal('margin_percent', 5, 2)->nullable();

            $table->boolean('is_local')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'is_active'], 'idx_supplier_parts_supplier_active');
            $table->index(['part_id', 'part_oem_number_id'], 'idx_supplier_parts_part_oem');
            $table->index('supplier_part_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_parts');
    }
};
