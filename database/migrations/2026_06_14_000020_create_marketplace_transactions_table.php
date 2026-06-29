<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Commission metering. One row per billable marketplace event, written when a PO is accepted.
    // MarketplaceCommissionService computes the fee and (optionally) hands it to BillingService.
    public function up(): void
    {
        Schema::create('marketplace_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('buyer_vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('supplier_vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->decimal('gross_amount', 14, 2);
            $table->decimal('commission_rate', 5, 2)->default(0); // percent applied
            $table->decimal('commission_amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('UGX');
            // pending | invoiced | settled | waived
            $table->string('status', 12)->default('pending')->index();
            $table->timestamps();
            $table->index(['supplier_vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_transactions');
    }
};
