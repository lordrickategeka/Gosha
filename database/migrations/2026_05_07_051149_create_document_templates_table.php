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
        if (!Schema::hasTable('document_templates')) {
            Schema::create('document_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->string('name'); // "Modern Invoice Blue"
                $table->enum('document_type', ['invoice', 'work_order', 'quotation', 'receipt', 'report']);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);

                // Template schema (JSON)
                $table->json('template_schema');

                // Page settings
                $table->string('page_size', 20)->default('A4'); // A4, Letter, A5
                $table->string('orientation', 20)->default('portrait'); // portrait, landscape
                $table->json('margins')->nullable(); // {top, right, bottom, left}

                // Branding
                $table->string('primary_color', 7)->default('#3B82F6');
                $table->string('secondary_color', 7)->default('#1E40AF');
                $table->string('font_family', 50)->default('Inter');
                $table->integer('font_size')->default(10);

                // Audit
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                // Indexes
                $table->index(['vendor_id', 'document_type']);
                $table->index(['vendor_id', 'is_default', 'document_type']);
            });
        }

        // Optional: Usage tracking
        if (!Schema::hasTable('template_usage_logs')) {
            Schema::create('template_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('document_templates')->cascadeOnDelete();
                $table->string('document_type', 50);
                $table->unsignedBigInteger('document_id'); // invoice_id, work_order_id, etc.
                $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('generated_at')->useCurrent();

                $table->index(['template_id', 'generated_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('template_usage_logs');
        Schema::dropIfExists('document_templates');
    }
};
