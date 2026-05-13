<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_check_id')->constrained()->cascadeOnDelete();
            $table->string('section');
            $table->string('item_name');
            $table->enum('status', ['ok', 'needs_attention', 'n_a'])->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['quality_check_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_check_items');
    }
};
