<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('customer_type', ['individual', 'corporate'])->default('individual');
            $table->string('company_name')->nullable();
            $table->string('tin')->nullable(); // Tax Identification Number
            $table->integer('loyalty_points')->default(0);
            $table->decimal('credit_balance', 12, 2)->default(0);
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'phone']);
            $table->index(['vendor_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
