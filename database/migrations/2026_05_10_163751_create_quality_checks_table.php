<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspector_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('inspection_date')->nullable();
            $table->enum('status', ['pending', 'completed', 'passed', 'has_issues'])->default('pending');
            $table->text('general_notes')->nullable();
            $table->string('signed_file_path')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'status']);
            $table->index(['vendor_id', 'branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_checks');
    }
};
