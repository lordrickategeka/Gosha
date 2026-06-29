<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Bulk pricing: min_qty -> unit_price. Resolved highest-applicable-tier wins.
    public function up(): void
    {
        Schema::create('listing_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_listing_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_qty');
            $table->decimal('unit_price', 14, 2);
            $table->timestamps();
            $table->index(['marketplace_listing_id', 'min_qty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_price_tiers');
    }
};
