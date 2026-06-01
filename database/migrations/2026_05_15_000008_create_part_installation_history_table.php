<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('part_installation_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');

            $table->foreignId('part_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('part_oem_number_id')->nullable()->constrained('part_oem_numbers')->nullOnDelete();
            $table->foreignId('supplier_part_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_order_part_source_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('installed_at')->nullable();

            $table->enum('fit_status', ['fitted_ok', 'failed', 'modified', 'unknown'])->default('unknown');
            $table->boolean('was_returned')->default(false);
            $table->text('fitment_notes')->nullable();
            $table->text('failure_reason')->nullable();

            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicle_id', 'fit_status'], 'idx_pih_vehicle_fitstatus');
            $table->index(['part_id', 'part_oem_number_id'], 'idx_pih_part_oem');
            $table->index(['supplier_part_id', 'fit_status'], 'idx_pih_supplier_fitstatus');
            $table->index('installed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_installation_history');
    }
};
