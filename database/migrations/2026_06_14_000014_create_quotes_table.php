<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();          // QT-2025-000001
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('currency', 3)->default('UGX');
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            // draft | submitted | awarded | rejected | expired | withdrawn
            $table->string('status', 12)->default('draft')->index();
            $table->timestamps();
            $table->unique(['rfq_id', 'supplier_vendor_id']);   // one quote per supplier per RFQ
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
