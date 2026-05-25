<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exchange_rates')) {
            return;
        }

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('to_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 15, 6);
            $table->date('effective_date');
            $table->enum('source', ['manual', 'api', 'bank'])->default('manual');
            $table->timestamps();

            // Indexes
            $table->index(['from_currency_id', 'to_currency_id', 'effective_date'], 'ex_rates_from_to_date_idx');
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
