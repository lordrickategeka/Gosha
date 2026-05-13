<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique(); // whatsapp, email, flutterwave
            $table->boolean('is_active')->default(false);
            $table->json('credentials')->nullable(); // encrypted API keys, tokens, secrets
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('provider');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_integrations');
    }
};
