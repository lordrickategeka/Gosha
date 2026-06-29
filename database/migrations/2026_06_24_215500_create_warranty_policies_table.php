<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('provider_name');
            $table->enum('coverage_type', ['bumper_to_bumper', 'powertrain', 'parts_specific']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('max_mileage')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_policies');
    }
};
