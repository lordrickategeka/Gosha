<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('part_oem_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            $table->string('oem_part_number', 64);
            $table->string('brand_type', 50)->default('OEM'); // OEM, Aftermarket
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['part_id', 'oem_part_number']);
            $table->index('oem_part_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_oem_numbers');
    }
};
