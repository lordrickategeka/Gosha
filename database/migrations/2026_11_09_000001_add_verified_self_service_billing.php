<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_plans', fn (Blueprint $table) => $table->boolean('is_trial')->default(false));
        Schema::table('vendor_subscriptions', fn (Blueprint $table) => $table->boolean('self_service')->default(false)->index());
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable();
        });
        Schema::create('subscription_payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('vendor_platform_invoices')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('tx_ref')->nullable()->unique();
            $table->string('environment');
            $table->string('status')->default('initiated')->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->text('checkout_url')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('subscription_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->string('dedupe_key')->unique();
            $table->string('subject');
            $table->text('message');
            $table->timestamp('available_at')->index();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->string('last_error')->nullable();
            $table->timestamps();
        });
        DB::table('pricing_plans')->insert([
            'name' => '14-day free trial', 'slug' => 'garagehq-14-day-trial',
            'description' => 'Explore GarageHQ with your team. No card and no automatic charge.',
            'billing_model' => 'free', 'base_price' => 0, 'currency' => 'UGX',
            'billing_cycle' => 'monthly', 'is_trial' => true, 'has_trial' => true,
            'trial_days' => 14, 'trial_requires_card' => false, 'setup_fee' => 0,
            'is_active' => true, 'sort_order' => -1, 'max_branches' => 1, 'max_users' => 5,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_notices');
        Schema::dropIfExists('subscription_payment_attempts');
        // Preserve referenced plan records and customer subscriptions during rollback.
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('phone_verified_at'));
        Schema::table('vendor_subscriptions', fn (Blueprint $table) => $table->dropColumn('self_service'));
        Schema::table('pricing_plans', fn (Blueprint $table) => $table->dropColumn('is_trial'));
    }
};
