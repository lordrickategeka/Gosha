<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wash_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wash_bay_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_attendant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete(); // for combo flow
            $table->string('order_number')->unique();
            $table->enum('wash_type', ['basic', 'standard', 'premium', 'interior', 'exterior', 'engine', 'full_detail', 'custom'])->default('standard');
            $table->enum('status', ['queued', 'in_progress', 'completed', 'cancelled'])->default('queued');
            $table->enum('source', ['walk_in', 'combo', 'appointment'])->default('walk_in');
            $table->enum('priority', ['normal', 'priority'])->default('normal');
            $table->integer('queue_position')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
            $table->index(['branch_id', 'queue_position']);
            $table->index('work_order_id');
        });

        Schema::create('wash_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wash_order_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();

            $table->index('wash_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wash_order_items');
        Schema::dropIfExists('wash_orders');
    }
};
