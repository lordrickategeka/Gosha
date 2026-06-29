<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('liters', 8, 2);
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('odometer_reading');
            $table->enum('source', ['manual_entry', 'obd_dongle', 'driver_app'])->default('manual_entry');
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index(['vehicle_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};
