<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop any partial state from a previous failed run
        Schema::disableForeignKeyConstraints();
        foreach ([
            'platform_features', 'vendor_usage_logs', 'platform_payments',
            'platform_invoice_items', 'vendor_platform_invoices',
            'vendor_subscriptions', 'pricing_plans', 'platform_settings',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        // Platform-wide pricing configuration
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pricing plans defined by platform owner
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            // Billing model type
            $table->enum('billing_model', [
                'free',           // Free forever
                'one_time',       // Single payment
                'subscription',   // Recurring subscription
                'commission',     // Commission-based only
                'hybrid',         // Subscription + commission
                'pay_per_use',    // Pay per transaction/action
                'custom'          // Custom negotiated terms
            ])->default('subscription');

            // Subscription settings (when billing_model includes subscription)
            $table->decimal('base_price', 12, 2)->default(0);
            $table->string('currency', 3)->default('UGX');
            $table->enum('billing_cycle', [
                'daily', 'weekly', 'monthly', 'quarterly', 'semi_annual', 'yearly', 'custom'
            ])->default('monthly');
            $table->integer('billing_cycle_days')->nullable(); // For custom cycles

            // Commission settings (when billing_model includes commission)
            $table->decimal('commission_rate', 5, 2)->default(0); // Percentage
            $table->enum('commission_base', [
                'gross_revenue',      // Commission on total revenue
                'net_revenue',        // Commission on revenue minus expenses
                'work_order_total',   // Per work order total
                'wash_order_total',   // Per wash order total
                'invoice_total',      // Per invoice total
                'payment_received'    // Per payment collected
            ])->nullable();
            $table->decimal('commission_min', 12, 2)->nullable(); // Minimum commission per period
            $table->decimal('commission_max', 12, 2)->nullable(); // Maximum commission cap

            // Pay-per-use settings
            $table->decimal('price_per_work_order', 10, 2)->default(0);
            $table->decimal('price_per_wash_order', 10, 2)->default(0);
            $table->decimal('price_per_invoice', 10, 2)->default(0);
            $table->decimal('price_per_user', 10, 2)->default(0);
            $table->decimal('price_per_branch', 10, 2)->default(0);
            $table->decimal('price_per_sms', 10, 2)->default(0);

            // Usage limits (null = unlimited)
            $table->integer('max_branches')->nullable();
            $table->integer('max_users')->nullable();
            $table->integer('max_customers')->nullable();
            $table->integer('max_work_orders_per_month')->nullable();
            $table->integer('max_wash_orders_per_month')->nullable();
            $table->integer('max_inventory_items')->nullable();
            $table->integer('max_sms_per_month')->nullable();
            $table->integer('storage_limit_mb')->nullable();

            // Feature flags (JSON - what features are included)
            $table->json('features')->nullable();

            // Trial settings
            $table->boolean('has_trial')->default(false);
            $table->integer('trial_days')->default(14);
            $table->boolean('trial_requires_card')->default(false);

            // Setup fee
            $table->decimal('setup_fee', 12, 2)->default(0);
            $table->boolean('setup_fee_waivable')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });

        // Vendor subscriptions (active plans for each vendor)
        Schema::create('vendor_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('pricing_plan_id')->constrained()->onDelete('restrict');

            $table->enum('status', [
                'trial',
                'active',
                'past_due',
                'paused',
                'cancelled',
                'expired'
            ])->default('trial');

            // Billing period
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            // Custom overrides (null = use plan defaults)
            $table->decimal('custom_base_price', 12, 2)->nullable();
            $table->decimal('custom_commission_rate', 5, 2)->nullable();
            $table->json('custom_limits')->nullable(); // Override plan limits
            $table->json('custom_features')->nullable(); // Additional features
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('discount_reason')->nullable();

            // Payment tracking
            $table->decimal('balance', 12, 2)->default(0); // Outstanding balance
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('next_billing_date')->nullable();

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('cancel_at_period_end')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['vendor_id', 'status']);
        });

        // Platform billing records (invoices from platform to vendors)
        Schema::create('vendor_platform_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_subscription_id')->nullable()->constrained()->onDelete('set null');

            $table->string('invoice_number')->unique();
            $table->enum('type', [
                'subscription',
                'commission',
                'usage',
                'setup_fee',
                'overage',
                'addon',
                'credit',
                'refund'
            ]);

            $table->enum('status', [
                'draft',
                'pending',
                'paid',
                'partially_paid',
                'overdue',
                'cancelled',
                'refunded'
            ])->default('pending');

            // Billing period this invoice covers
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            // Amounts
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->string('currency', 3)->default('UGX');

            $table->date('issue_date');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Usage details, commission calculations, etc.

            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->index('due_date');
        });

        // Platform invoice line items
        Schema::create('platform_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_invoice_id')->constrained()->onDelete('cascade');

            $table->string('description');
            $table->string('type')->nullable(); // subscription, commission, usage, etc.
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);

            $table->json('metadata')->nullable(); // Details about the charge

            $table->timestamps();
        });

        // Platform payments (payments from vendors to platform)
        Schema::create('platform_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('platform_invoice_id')->nullable()->constrained()->onDelete('set null');

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('UGX');

            $table->enum('payment_method', [
                'cash',
                'mobile_money',
                'bank_transfer',
                'card',
                'cheque',
                'platform_credit',
                'other'
            ]);

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'refunded'
            ])->default('pending');

            $table->string('reference_number')->nullable();
            $table->string('transaction_id')->nullable(); // External payment gateway ID
            $table->string('mobile_money_number')->nullable();
            $table->string('bank_reference')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');

            $table->text('notes')->nullable();
            $table->json('gateway_response')->nullable();

            $table->timestamps();

            $table->index(['vendor_id', 'status']);
        });

        // Usage tracking for pay-per-use and commission calculations
        Schema::create('vendor_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');

            $table->enum('metric', [
                'work_order',
                'wash_order',
                'invoice',
                'payment_received',
                'sms_sent',
                'user_added',
                'branch_added',
                'customer_added',
                'storage_used'
            ]);

            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('amount', 12, 2)->default(0); // Revenue amount if applicable

            $table->string('reference_type')->nullable(); // Model type
            $table->unsignedBigInteger('reference_id')->nullable(); // Model ID

            $table->date('usage_date');
            $table->string('billing_period')->nullable(); // e.g., "2024-01" for monthly

            $table->boolean('billed')->default(false);
            $table->foreignId('platform_invoice_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamps();

            $table->index(['vendor_id', 'billing_period', 'billed']);
            $table->index(['metric', 'usage_date']);
        });

        // Feature flags/modules that can be enabled/disabled per plan
        Schema::create('platform_features', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('general');
            $table->boolean('is_core')->default(false); // Core features can't be disabled
            $table->decimal('addon_price', 12, 2)->default(0); // Price if sold separately
            $table->enum('addon_billing', ['one_time', 'monthly', 'yearly'])->default('monthly');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add plan reference to vendors table
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'pricing_plan_id')) {
                $table->foreignId('pricing_plan_id')->nullable()->constrained()->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['pricing_plan_id']);
            $table->dropColumn('pricing_plan_id');
        });

        Schema::dropIfExists('platform_features');
        Schema::dropIfExists('vendor_usage_logs');
        Schema::dropIfExists('platform_payments');
        Schema::dropIfExists('platform_invoice_items');
        Schema::dropIfExists('vendor_platform_invoices');
        Schema::dropIfExists('vendor_subscriptions');
        Schema::dropIfExists('pricing_plans');
        Schema::dropIfExists('platform_settings');
    }
};
