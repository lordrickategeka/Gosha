<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('registration_number');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->year('year')->nullable();
            $table->string('color')->nullable();
            $table->string('vin')->nullable();
            $table->string('engine_number')->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid', 'other'])->nullable();
            $table->string('transmission')->nullable();
            $table->integer('mileage')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('registration_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
