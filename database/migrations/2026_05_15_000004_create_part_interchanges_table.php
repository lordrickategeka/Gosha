<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('part_interchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_part_oem_number_id')->constrained('part_oem_numbers')->onDelete('cascade');
            $table->foreignId('to_part_oem_number_id')->constrained('part_oem_numbers')->onDelete('cascade');
            $table->enum('interchange_type', ['equivalent', 'compatible', 'superseded']);
            $table->integer('year_from')->nullable();
            $table->integer('year_to')->nullable();
            $table->string('market_region', 50)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['from_part_oem_number_id', 'interchange_type'], 'idx_part_interchanges_from_type');
            $table->index(['to_part_oem_number_id', 'interchange_type'], 'idx_part_interchanges_to_type');
            $table->index('market_region');
            $table->unique(
                ['from_part_oem_number_id', 'to_part_oem_number_id', 'interchange_type'],
                'uniq_part_interchange_pair_type'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_interchanges');
    }
};
