<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debit_note_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('debit_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('item_type', ['labor', 'part'])->default('part');
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->enum('customer_decision', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['debit_note_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_note_items');
    }
};
