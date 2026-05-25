<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        if (Schema::hasTable('inventory_items')) {
            return;
        }

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_type_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('inventory_items');
    }
};
