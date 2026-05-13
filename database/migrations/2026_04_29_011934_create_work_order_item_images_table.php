<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_order_item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_item_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();

            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('inventory_consumed')->default(false);
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['inventory_item_id', 'inventory_consumed'], 'woii_inv_item_consumed_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_item_images');
    }
};
