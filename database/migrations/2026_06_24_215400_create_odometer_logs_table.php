<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odometer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->timestamp('reading_date');
            $table->integer('odometer_value');
            $table->integer('engine_hours')->nullable();
            $table->enum('source', ['manual_entry', 'obd_dongle', 'driver_app'])->default('manual_entry');
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index(['vehicle_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odometer_logs');
    }
};
