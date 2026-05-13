<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('inventory_categories')->nullOnDelete();

            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['service_parts', 'wash_supplies', 'consumables', 'tools'])->default('service_parts');
            $table->text('description')->nullable();
             $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['vendor_id', 'type']);
            $table->unique(['vendor_id', 'name', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_categories');
    }
};
