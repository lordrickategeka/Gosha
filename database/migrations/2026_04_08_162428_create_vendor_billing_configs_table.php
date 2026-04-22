<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_billing_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->enum('billing_model', ['subscription', 'transaction_fee', 'commission_cut', 'hybrid', 'none'])->default('none');
            $table->decimal('subscription_amount', 10, 2)->nullable();
            $table->enum('subscription_interval', ['monthly', 'yearly'])->nullable();
            $table->decimal('transaction_fee_percent', 5, 2)->nullable();
            $table->decimal('transaction_fee_flat', 10, 2)->nullable();
            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->date('next_billing_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_billing_configs');
    }
};
