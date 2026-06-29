<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_part_sources', function (Blueprint $table) {
            $table->id();
$table->foreignId('work_order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_part_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->unsigned()->nullable();

            $table->string('source_name', 255)->nullable();
            $table->string('source_link', 2048)->nullable();
            $table->string('source_part_number', 100)->nullable();

            $table->decimal('supplier_price', 14, 2)->nullable();
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->decimal('duty_cost', 14, 2)->default(0);
            $table->decimal('clearing_cost', 14, 2)->default(0);
            $table->decimal('margin_amount', 14, 2)->default(0);
            $table->decimal('margin_percent', 5, 2)->nullable();
            $table->decimal('total_landed_cost', 14, 2)->nullable();

            $table->boolean('is_local')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->unsignedTinyInteger('confidence_score')->nullable();
            $table->json('reason_codes')->nullable();

            $table->string('availability', 50)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->string('warranty_text', 255)->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['work_order_item_id', 'is_recommended'], 'idx_wops_item_recommended');
            $table->index(['work_order_item_id', 'confidence_score'], 'idx_wops_item_confidence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_part_sources');
    }
};
