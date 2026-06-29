<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Platform-owned taxonomy for the canonical catalog. NOT vendor scoped.
    // If an inventory category table already exists, repoint catalog_products.category_id
    // and skip this migration (see README "Existing schema" notes).
    public function up(): void
    {
        if (Schema::hasTable('part_categories')) {
            return;
        }

        Schema::create('part_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('part_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_categories');
    }
};
