<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_bay_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_number')->unique();
            $table->enum('type', ['repair', 'service', 'diagnostics', 'bodywork', 'electrical', 'ac', 'tyres', 'other'])->default('service');
            $table->enum('status', ['open', 'in_progress', 'quality_check', 'ready', 'delivered', 'cancelled'])->default('open');
            $table->boolean('is_combo')->default(false);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->integer('mileage_in')->nullable();
            $table->integer('mileage_out')->nullable();
            $table->text('customer_notes')->nullable(); // what customer reported
            $table->text('technician_notes')->nullable(); // findings/work done
            $table->timestamp('estimated_completion')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
            $table->index(['branch_id', 'created_at']);
            $table->index(['customer_id', 'status']);
        });

        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', ['labor', 'part']);
            $table->string('description');
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();

            $table->index('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
        Schema::dropIfExists('work_orders');
    }
};
