<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expense_approvals')) {
            return;
        }

        Schema::create('expense_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approval_level_id')->constrained('expense_approval_levels')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['expense_id', 'status']);
            $table->index(['approver_id', 'status']);
            $table->unique(['expense_id', 'approval_level_id', 'approver_id'], 'exp_approvals_exp_lvl_appr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_approvals');
    }
};
