<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expenses')) {
            return;
        }

        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('expenses', 'expense_type')) {
                $table->enum('expense_type', ['business', 'petty_cash', 'employee_claim', 'payroll'])
                    ->nullable()
                    ->after('branch_id');
            }

            if (! Schema::hasColumn('expenses', 'currency')) {
                $table->string('currency', 3)->default('UGX')->after('amount');
            }

            if (! Schema::hasColumn('expenses', 'exchange_rate')) {
                $table->decimal('exchange_rate', 10, 4)->default(1)->after('currency');
            }

            if (! Schema::hasColumn('expenses', 'amount_in_base_currency')) {
                $table->decimal('amount_in_base_currency', 15, 2)->nullable()->after('exchange_rate');
            }

            if (! Schema::hasColumn('expenses', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('amount_in_base_currency');
            }

            if (! Schema::hasColumn('expenses', 'tax_percentage')) {
                $table->decimal('tax_percentage', 5, 2)->default(0)->after('tax_amount');
            }

            if (! Schema::hasColumn('expenses', 'tax_inclusive')) {
                $table->boolean('tax_inclusive')->default(false)->after('tax_percentage');
            }

            if (! Schema::hasColumn('expenses', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->nullable()->after('tax_inclusive');
            }

            if (! Schema::hasColumn('expenses', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('expenses', 'current_approval_level')) {
                $table->unsignedInteger('current_approval_level')->nullable()->after('status');
            }

            if (! Schema::hasColumn('expenses', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('current_approval_level');
            }

            if (! Schema::hasColumn('expenses', 'recurrence_config')) {
                $table->json('recurrence_config')->nullable()->after('is_recurring');
            }

            if (! Schema::hasColumn('expenses', 'parent_expense_id')) {
                $table->foreignId('parent_expense_id')->nullable()->after('recurrence_config')
                    ->constrained('expenses')->nullOnDelete();
            }

            if (! Schema::hasColumn('expenses', 'claimed_by')) {
                $table->foreignId('claimed_by')->nullable()->after('parent_expense_id')
                    ->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('expenses', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('claimed_by')
                    ->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('expenses', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('approved_by')
                    ->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('expenses', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }

            if (! Schema::hasColumn('expenses', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }

            if (! Schema::hasColumn('expenses', 'paid_by')) {
                $table->foreignId('paid_by')->nullable()->after('rejection_reason')
                    ->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('expenses', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('paid_by');
            }

            if (! Schema::hasColumn('expenses', 'metadata')) {
                $table->json('metadata')->nullable()->after('paid_at');
            }
        });

        if (Schema::hasColumn('expenses', 'vendor_id')) {
            DB::statement('update expenses e inner join branches b on e.branch_id = b.id set e.vendor_id = b.vendor_id where e.vendor_id is null');
        }

        // Expand legacy status enum to support the current workflow states.
        DB::statement("ALTER TABLE expenses MODIFY COLUMN status ENUM('draft','pending','pending_approval','approved','rejected','paid','cancelled') NOT NULL DEFAULT 'draft'");

    }

    public function down(): void
    {
        if (! Schema::hasTable('expenses')) {
            return;
        }

        // Keep down migration conservative for production safety.
        DB::statement("ALTER TABLE expenses MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved'");
    }
};
