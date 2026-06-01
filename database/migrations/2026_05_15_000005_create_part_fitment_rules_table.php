<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('part_fitment_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            $table->foreignId('part_oem_number_id')->nullable()->constrained('part_oem_numbers')->nullOnDelete();

            $table->string('make', 50)->nullable();
            $table->string('model', 50)->nullable();
            $table->integer('year_from')->nullable();
            $table->integer('year_to')->nullable();
            $table->string('chassis_code', 50)->nullable();
            $table->string('engine_code', 50)->nullable();
            $table->string('transmission', 50)->nullable();
            $table->string('drivetrain', 50)->nullable();
            $table->string('market_region', 50)->nullable();

            $table->unsignedTinyInteger('fitment_match_weight')->default(10);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['part_id', 'is_active'], 'idx_fitment_part_active');
            $table->index(['make', 'model', 'year_from', 'year_to'], 'idx_fitment_make_model_year');
            $table->index(['engine_code', 'transmission', 'drivetrain'], 'idx_fitment_powertrain');
            $table->index('market_region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_fitment_rules');
    }
};
