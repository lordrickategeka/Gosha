<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['service', 'repair', 'diagnostics', 'bodywork', 'electrical', 'ac', 'tyres', 'other'])->default('service');
            $table->integer('estimated_duration_minutes')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'is_active']);
        });

        Schema::create('service_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_template_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', ['labor', 'part']);
            $table->string('description');
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_template_items');
        Schema::dropIfExists('service_templates');
    }
};
