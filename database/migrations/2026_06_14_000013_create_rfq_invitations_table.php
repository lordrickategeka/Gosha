<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Supports both open and targeted RFQs. For open RFQs, rows are created lazily
    // when a supplier engages; for targeted, the buyer pre-populates invitees.
    public function up(): void
    {
        Schema::create('rfq_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_vendor_id')->constrained('vendors')->cascadeOnDelete();
            // invited | viewed | quoted | declined
            $table->string('status', 12)->default('invited');
            $table->timestamps();
            $table->unique(['rfq_id', 'supplier_vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_invitations');
    }
};
