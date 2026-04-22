<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Wash packages - predefined wash services with pricing
        Schema::create('wash_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Basic Wash, Premium Wash, Full Detail
            $table->enum('wash_type', ['basic', 'standard', 'premium', 'interior', 'exterior', 'engine', 'full_detail', 'custom'])->default('standard');
            $table->text('description')->nullable();
            $table->json('includes')->nullable(); // list of what's included
            $table->integer('estimated_duration_minutes')->default(30);
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'is_active']);
        });

        // Vehicle size pricing adjustments
        Schema::create('vehicle_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Sedan, SUV, Truck, Bus, etc.
            $table->decimal('price_multiplier', 5, 2)->default(1.00); // 1.0, 1.25, 1.5, etc.
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_categories');
        Schema::dropIfExists('wash_packages');
    }
};
