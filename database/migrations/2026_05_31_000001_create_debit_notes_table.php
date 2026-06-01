<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();

            $table->string('debit_note_number')->unique();
            $table->uuid('approval_token')->unique();

            $table->enum('status', [
                'draft',
                'sent',
                'partially_approved',
                'approved',
                'rejected',
                'applied',
            ])->default('draft');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['work_order_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('approval_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_notes');
    }
};
