<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtc_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // e.g., P0301
            $table->text('description')->nullable();
            $table->timestamp('logged_at');
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtc_logs');
    }
};
