<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('vat_registered')->default(false)->after('address');
            $table->string('vat_number')->nullable()->after('vat_registered');
            $table->decimal('default_vat_rate', 5, 2)->nullable()->default(0)->after('vat_number');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['vat_registered', 'vat_number', 'default_vat_rate']);
        });
    }
};
