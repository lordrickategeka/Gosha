<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_approval_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_chain_id')->constrained('expense_approval_chains')->cascadeOnDelete();
            $table->unsignedInteger('level_number');

            // Approver definition
            $table->enum('approver_role', ['manager', 'admin', 'finance_officer', 'owner', 'specific_user'])->nullable();
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Level behavior
            $table->boolean('require_all')->default(false); // If multiple approvers, all must approve
            $table->boolean('can_skip')->default(false);
            $table->json('skip_conditions')->nullable();
            $table->unsignedInteger('timeout_hours')->nullable();

            $table->timestamps();

            // Ensure level numbers are unique within a chain
            $table->unique(['approval_chain_id', 'level_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_approval_levels');
    }
};
