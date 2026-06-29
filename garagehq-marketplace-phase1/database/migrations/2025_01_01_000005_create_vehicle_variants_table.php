<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained()->cascadeOnDelete();
            $table->string('name');                       // e.g. "2.0 TDI", "1.8T"
            $table->unsignedSmallInteger('year_from')->nullable();
            $table->unsignedSmallInteger('year_to')->nullable();
            $table->string('engine_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['vehicle_model_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_variants');
    }
};
