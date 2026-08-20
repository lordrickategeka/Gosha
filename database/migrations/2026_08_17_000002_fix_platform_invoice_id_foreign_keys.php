<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * platform_invoice_items, platform_payments, and vendor_usage_logs were all
 * created with a bare `constrained()` on their platform_invoice_id column.
 * Laravel's convention-based table inference resolved that to the legacy
 * `platform_invoices` table (a naming coincidence — a real table that
 * happens to exist), instead of the `vendor_platform_invoices` table these
 * columns actually reference everywhere in application code
 * (VendorPlatformInvoice::items()/payments()/usageLogs()). All three tables
 * are empty in production use since nothing could ever insert into them
 * without hitting this constraint, so this is a pure schema correction.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_invoice_items', function (Blueprint $table) {
            $table->dropForeign('platform_invoice_items_platform_invoice_id_foreign');
            $table->foreign('platform_invoice_id')->references('id')->on('vendor_platform_invoices')->cascadeOnDelete();
        });

        Schema::table('platform_payments', function (Blueprint $table) {
            $table->dropForeign('platform_payments_platform_invoice_id_foreign');
            $table->foreign('platform_invoice_id')->references('id')->on('vendor_platform_invoices')->nullOnDelete();
        });

        Schema::table('vendor_usage_logs', function (Blueprint $table) {
            $table->dropForeign('vendor_usage_logs_platform_invoice_id_foreign');
            $table->foreign('platform_invoice_id')->references('id')->on('vendor_platform_invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('platform_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['platform_invoice_id']);
            $table->foreign('platform_invoice_id')->references('id')->on('platform_invoices')->cascadeOnDelete();
        });

        Schema::table('platform_payments', function (Blueprint $table) {
            $table->dropForeign(['platform_invoice_id']);
            $table->foreign('platform_invoice_id')->references('id')->on('platform_invoices')->nullOnDelete();
        });

        Schema::table('vendor_usage_logs', function (Blueprint $table) {
            $table->dropForeign(['platform_invoice_id']);
            $table->foreign('platform_invoice_id')->references('id')->on('platform_invoices')->nullOnDelete();
        });
    }
};
