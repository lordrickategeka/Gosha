<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'payment_terms_days')) {
                $table->unsignedInteger('payment_terms_days')->default(30)->after('notes');
            }

            if (! Schema::hasColumn('suppliers', 'opening_balance')) {
                $table->decimal('opening_balance', 12, 2)->default(0)->after('payment_terms_days');
            }

            if (! Schema::hasColumn('suppliers', 'current_balance')) {
                $table->decimal('current_balance', 12, 2)->default(0)->after('opening_balance');
            }

            if (! Schema::hasColumn('suppliers', 'credit_limit')) {
                $table->decimal('credit_limit', 12, 2)->default(0)->after('current_balance');
            }

            if (! Schema::hasColumn('suppliers', 'last_statement_at')) {
                $table->timestamp('last_statement_at')->nullable()->after('credit_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            foreach (['last_statement_at', 'credit_limit', 'current_balance', 'opening_balance', 'payment_terms_days'] as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
