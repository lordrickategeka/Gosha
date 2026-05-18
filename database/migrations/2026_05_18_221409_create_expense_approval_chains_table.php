<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_approval_chains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();

            // Chain applicability conditions
            $table->enum('expense_type', ['business', 'petty_cash', 'employee_claim', 'payroll'])->nullable();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['vendor_id', 'is_active']);
            $table->index(['vendor_id', 'is_default']);
            $table->index(['vendor_id', 'expense_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_approval_chains');
    }
};
