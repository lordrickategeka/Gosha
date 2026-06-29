<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (from earlier partial migration run)
        if (Schema::hasTable('vehicle_models')) {
            return;
        }

        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->string('make');
            $table->string('model_name');
            $table->string('engine_code')->nullable();
            $table->enum('fuel_type', ['gasoline', 'diesel', 'flex_fuel', 'hev', 'phev', 'bev'])->nullable();
            $table->enum('transmission_type', ['manual', 'automatic', 'cvt', 'dual_clutch'])->nullable();
            $table->decimal('oil_capacity_liters', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['make', 'model_name']);
            $table->unique(['make', 'model_name', 'engine_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};
