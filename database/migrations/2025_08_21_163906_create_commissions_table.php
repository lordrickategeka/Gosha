<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
             $table->foreignId('staff_id')->constrained('staff');
            $table->foreignId('job_card_id')->constrained('job_cards');
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types');
            $table->foreignId('service_type_id')->constrained('service_types');
            $table->decimal('commission_amount', 8, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('commission_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
