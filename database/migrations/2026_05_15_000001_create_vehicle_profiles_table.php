<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('vin', 64)->nullable();
            $table->string('chassis_code', 50)->nullable();
            $table->string('make', 50)->nullable();
            $table->string('model', 50)->nullable();
            $table->string('trim', 50)->nullable();
            $table->integer('year')->nullable();
            $table->string('engine_code', 50)->nullable();
            $table->string('transmission', 50)->nullable();
            $table->string('drivetrain', 50)->nullable();
            $table->string('market_region', 50)->nullable();
            $table->string('decoded_source', 100)->nullable();
            $table->timestamp('decoded_at')->nullable();
            $table->timestamps();

            $table->index('vin');
            $table->index('chassis_code');
            $table->index(['vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_profiles');
    }
};
