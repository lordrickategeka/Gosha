<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('bill_number')->unique();
            $table->string('supplier_invoice_number')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->enum('status', ['draft', 'received', 'partially_paid', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index(['branch_id', 'due_date']);
        });

        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();

            $table->index('bill_id');
        });

        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque', 'credit_card', 'other'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->date('payment_date');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['bill_id', 'status']);
            $table->index(['supplier_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
    }
};
