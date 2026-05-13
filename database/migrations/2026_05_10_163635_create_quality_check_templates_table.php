<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_check_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('section'); // exterior, interior, engine_compartment, underbody_suspension, road_test, general_notes
            $table->string('item_name');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // true for system defaults, false for custom
            $table->timestamps();

            $table->index(['vendor_id', 'section', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_check_templates');
    }
};
