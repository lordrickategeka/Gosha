<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // garage | car_wash | supplier | hybrid
            // 'hybrid' lets a garage also sell surplus stock later with no schema change.
            $table->string('vendor_type', 20)->default('garage')->after('id')->index();

            // Supplier profile (only meaningful when vendor_type in [supplier, hybrid]).
            $table->boolean('is_verified_supplier')->default(false)->after('vendor_type');
            // Denormalised aggregates so listing/browse queries never aggregate on read.
            $table->decimal('supplier_rating_avg', 3, 2)->default(0)->after('is_verified_supplier');
            $table->unsignedInteger('supplier_rating_count')->default(0)->after('supplier_rating_avg');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['vendor_type', 'is_verified_supplier', 'supplier_rating_avg', 'supplier_rating_count']);
        });
    }
};
