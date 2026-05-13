<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('vendor_id')
                    ->constrained()->nullOnDelete();

                $table->index(['branch_id', 'is_active']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropIndex(['branch_id', 'is_active']);
                $table->dropColumn('branch_id');
            }
        });
    }
};
