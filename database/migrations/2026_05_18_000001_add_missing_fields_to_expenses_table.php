<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();

                // Fields currently used by ExpenseCategory model.
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);

                // Requested additional fields.
                $table->string('code', 50)->unique()->nullable();
                $table->enum('expense_type', ['business', 'petty_cash', 'employee_claim', 'payroll'])->nullable();
                $table->string('color', 7)->default('#6B7280');
                $table->string('icon', 50)->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('expense_categories')->nullOnDelete();
                $table->decimal('auto_approval_threshold', 15, 2)->nullable();
                $table->boolean('requires_receipt')->default(false);
                $table->boolean('requires_tax_invoice')->default(false);
                $table->string('gl_account_code', 50)->nullable();
                $table->unsignedInteger('display_order')->default(0);

                $table->timestamps();
                $table->softDeletes();

                $table->index(['vendor_id', 'is_active']);
                $table->index(['vendor_id', 'expense_type']);
            });

            return;
        }

        Schema::table('expense_categories', function (Blueprint $table) {
            // Ensure model-required fields exist when table already exists.
            if (! Schema::hasColumn('expense_categories', 'vendor_id')) {
                $table->foreignId('vendor_id')->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('expense_categories', 'name')) {
                $table->string('name')->after('vendor_id');
            }

            if (! Schema::hasColumn('expense_categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('expense_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }

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

            if (! Schema::hasColumn('expense_categories', 'gl_account_code')) {
                $table->string('gl_account_code', 50)->after('requires_tax_invoice')->nullable();
            }

            if (! Schema::hasColumn('expense_categories', 'display_order')) {
                $table->unsignedInteger('display_order')->after('gl_account_code')->default(0);
            }
        });

        Schema::table('expense_categories', function (Blueprint $table) {
            if (Schema::hasColumn('expense_categories', 'vendor_id') && Schema::hasColumn('expense_categories', 'expense_type')) {
                $table->index(['vendor_id', 'expense_type']);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('expense_categories')) {
            return;
        }

        Schema::table('expense_categories', function (Blueprint $table) {
            if (Schema::hasColumn('expense_categories', 'parent_id')) {
                $table->dropForeign(['parent_id']);
            }

            if (Schema::hasColumn('expense_categories', 'vendor_id') && Schema::hasColumn('expense_categories', 'expense_type')) {
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
