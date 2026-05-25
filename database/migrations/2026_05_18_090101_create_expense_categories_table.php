<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select('PRAGMA index_list("' . $table . '")');

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            return ! empty(DB::select('show indexes from `' . $table . '` where Key_name = ?', [$indexName]));
        }

        return Schema::hasIndex($table, $indexName);
    }

    public function up(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            // Accounting integration
            if (! Schema::hasColumn('expense_categories', 'code')) {
                $table->string('code', 50)->after('name')->unique()->nullable();
            }

            // Type-specific categories
            if (! Schema::hasColumn('expense_categories', 'expense_type')) {
                $table->enum('expense_type', ['business', 'petty_cash', 'employee_claim', 'payroll'])
                    ->after('description')->nullable();
            }

            // UI enhancements
            if (! Schema::hasColumn('expense_categories', 'color')) {
                $table->string('color', 7)->after('expense_type')->default('#6B7280');
            }
            if (! Schema::hasColumn('expense_categories', 'icon')) {
                $table->string('icon', 50)->after('color')->nullable();
            }

            // Hierarchy support
            if (! Schema::hasColumn('expense_categories', 'parent_id')) {
                $table->foreignId('parent_id')->after('icon')->nullable()
                    ->constrained('expense_categories')->nullOnDelete();
            }

            // Approval & compliance rules
            if (! Schema::hasColumn('expense_categories', 'auto_approval_threshold')) {
                $table->decimal('auto_approval_threshold', 15, 2)->after('parent_id')->nullable();
            }
            if (! Schema::hasColumn('expense_categories', 'requires_receipt')) {
                $table->boolean('requires_receipt')->after('auto_approval_threshold')->default(false);
            }
            if (! Schema::hasColumn('expense_categories', 'requires_tax_invoice')) {
                $table->boolean('requires_tax_invoice')->after('requires_receipt')->default(false);
            }

            // Accounting integration
            if (! Schema::hasColumn('expense_categories', 'gl_account_code')) {
                $table->string('gl_account_code', 50)->after('requires_tax_invoice')->nullable();
            }

            // Display ordering
            if (! Schema::hasColumn('expense_categories', 'display_order')) {
                $table->unsignedInteger('display_order')->after('gl_account_code')->default(0);
            }

            // Index for performance
            if (
                Schema::hasColumn('expense_categories', 'vendor_id')
                && Schema::hasColumn('expense_categories', 'is_active')
                && ! $this->indexExists('expense_categories', 'expense_categories_vendor_id_is_active_index')
            ) {
                $table->index(['vendor_id', 'is_active']);
            }
            if (
                Schema::hasColumn('expense_categories', 'vendor_id')
                && Schema::hasColumn('expense_categories', 'expense_type')
                && ! $this->indexExists('expense_categories', 'expense_categories_vendor_id_expense_type_index')
            ) {
                $table->index(['vendor_id', 'expense_type']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            if (Schema::hasColumn('expense_categories', 'parent_id')) {
                $table->dropForeign(['parent_id']);
            }

            if (
                Schema::hasColumn('expense_categories', 'vendor_id')
                && Schema::hasColumn('expense_categories', 'is_active')
                && $this->indexExists('expense_categories', 'expense_categories_vendor_id_is_active_index')
            ) {
                $table->dropIndex(['vendor_id', 'is_active']);
            }
            if (
                Schema::hasColumn('expense_categories', 'vendor_id')
                && Schema::hasColumn('expense_categories', 'expense_type')
                && $this->indexExists('expense_categories', 'expense_categories_vendor_id_expense_type_index')
            ) {
                $table->dropIndex(['vendor_id', 'expense_type']);
            }

            $dropColumns = [];

            foreach ([
                'code',
                'expense_type',
                'color',
                'icon',
                'parent_id',
                'auto_approval_threshold',
                'requires_receipt',
                'requires_tax_invoice',
                'gl_account_code',
                'display_order',
            ] as $column) {
                if (Schema::hasColumn('expense_categories', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
