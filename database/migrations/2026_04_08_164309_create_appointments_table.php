<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('appointment_number')->nullable();
            $table->enum('type', ['service', 'wash', 'combo', 'diagnostics', 'estimate'])->default('service');
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->integer('duration_minutes')->default(60);
            $table->enum('status', ['scheduled', 'confirmed', 'checked_in', 'in_progress', 'completed', 'no_show', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->text('service_notes')->nullable(); // what they're coming in for
            $table->text('internal_notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'scheduled_date']);
            $table->index(['branch_id', 'status']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
