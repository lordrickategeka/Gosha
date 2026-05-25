<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expense_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->unique()->constrained()->cascadeOnDelete();

            $table->boolean('auto_create_enabled')->default(false);
            $table->unsignedInteger('notify_before_creation_days')->default(3);
            $table->json('notify_users')->nullable(); // Array of user IDs
            $table->boolean('require_approval_even_if_recurring')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_expense_settings');
    }
};
