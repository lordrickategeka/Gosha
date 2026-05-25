<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();

            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 100);
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->enum('attachment_type', ['receipt', 'invoice', 'supporting_doc', 'photo'])->default('receipt');

            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('uploaded_at');

            // Index
            $table->index('expense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_attachments');
    }
};
