<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->timestamp('supplier_requested_at')->nullable();
            $table->timestamp('supplier_reviewed_at')->nullable();
            $table->text('supplier_application_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', fn (Blueprint $table) => $table->dropColumn(['supplier_requested_at', 'supplier_reviewed_at', 'supplier_application_notes']));
    }
};
