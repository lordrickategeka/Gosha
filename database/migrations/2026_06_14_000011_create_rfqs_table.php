<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();          // RFQ-2025-000001
            // Buyer side. Named key, NOT global vendor scope.
            $table->foreignId('buyer_vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            // open = any supplier may quote; targeted = only invited suppliers (see rfq_invitations).
            $table->string('visibility', 10)->default('open');
            // draft | published | closed | awarded | cancelled
            $table->string('status', 15)->default('draft')->index();
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
            $table->index(['buyer_vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};
