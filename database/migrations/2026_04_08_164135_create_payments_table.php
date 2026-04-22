<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'mobile_money', 'card', 'bank_transfer', 'credit', 'cheque'])->default('cash');
            $table->string('provider')->nullable(); // MTN, Airtel, Visa, etc.
            $table->string('reference')->nullable(); // transaction ID
            $table->string('receipt_number')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_id', 'status']);
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
