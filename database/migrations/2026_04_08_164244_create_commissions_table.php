<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role'); // technician, attendant, cashier, etc.
            $table->enum('type', ['percentage', 'flat'])->default('percentage');
            $table->decimal('value', 10, 2); // percentage or flat amount
            $table->enum('applies_to', ['labor', 'parts', 'total', 'wash'])->default('labor');
            $table->decimal('minimum_threshold', 12, 2)->nullable(); // minimum job value to qualify
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'role', 'is_active']);
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commission_rule_id')->nullable()->constrained('commission_rules')->nullOnDelete();
            $table->string('reference_type'); // work_order, wash_order, invoice
            $table->unsignedBigInteger('reference_id');
            $table->decimal('base_amount', 12, 2); // the amount commission was calculated on
            $table->decimal('commission_amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('commission_rules');
    }
};
