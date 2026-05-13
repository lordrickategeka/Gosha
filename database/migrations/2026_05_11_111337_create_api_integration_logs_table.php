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
        Schema::create('api_integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_integration_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // test, send, webhook_received, etc.
            $table->string('status'); // success, failed, pending
            $table->json('details')->nullable(); // request/response data
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['api_integration_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_integration_logs');
    }
};
