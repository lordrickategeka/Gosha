<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->enum('lockdown_mode', ['limited', 'total'])->nullable()->after('pricing_plan_id');
            $table->unsignedInteger('grace_days_override')->nullable()->after('lockdown_mode');
        });

        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->boolean('auto_renew')->default(true)->after('next_billing_date');
            $table->timestamp('grace_ends_at')->nullable()->after('auto_renew');
            $table->timestamp('locked_at')->nullable()->after('grace_ends_at');
            $table->string('flutterwave_card_token')->nullable()->after('locked_at');
            $table->string('flutterwave_customer_email')->nullable()->after('flutterwave_card_token');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'auto_renew',
                'grace_ends_at',
                'locked_at',
                'flutterwave_card_token',
                'flutterwave_customer_email',
            ]);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['lockdown_mode', 'grace_days_override']);
        });
    }
};
