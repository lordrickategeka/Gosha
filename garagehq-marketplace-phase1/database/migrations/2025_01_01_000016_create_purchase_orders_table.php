<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();          // PO-2025-000001
            // Cross-tenant: both parties named explicitly. No global vendor scope.
            $table->foreignId('buyer_vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('supplier_vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Provenance of the PO: a direct listing buy or an awarded RFQ quote.
            $table->string('source_type', 15)->default('direct_listing'); // direct_listing | rfq_quote
            $table->unsignedBigInteger('source_id')->nullable();           // listing id or quote id

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('currency', 3)->default('UGX');

            // draft | sent | accepted | fulfilling | received | completed | cancelled
            $table->string('status', 15)->default('draft')->index();
            // unpaid | partial | paid  (recorded offline in Phase 1; gateway hooks slot in behind this)
            $table->string('payment_status', 10)->default('unpaid')->index();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['buyer_vendor_id', 'status']);
            $table->index(['supplier_vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
