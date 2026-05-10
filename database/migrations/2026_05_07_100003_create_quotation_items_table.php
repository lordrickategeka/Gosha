<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('item_type', ['labor', 'part'])->default('part');
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->boolean('vat_applicable')->default(true);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0); // ex-VAT: (qty * unit_price) - discount

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['quotation_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
