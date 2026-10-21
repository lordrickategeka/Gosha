<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_customer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['branch_id', 'customer_id']);
        });

        // A customer can be served at more than one branch, so the single
        // branch_id column is replaced by this pivot. Carry over any
        // existing assignment as that customer's primary branch first.
        DB::table('customers')
            ->whereNotNull('branch_id')
            ->get(['id', 'branch_id'])
            ->each(function ($customer) {
                DB::table('branch_customer')->insert([
                    'branch_id' => $customer->branch_id,
                    'customer_id' => $customer->id,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete()
                ->after('vendor_id');
        });

        DB::table('branch_customer')
            ->where('is_primary', true)
            ->get(['branch_id', 'customer_id'])
            ->each(function ($pivot) {
                DB::table('customers')
                    ->where('id', $pivot->customer_id)
                    ->update(['branch_id' => $pivot->branch_id]);
            });

        Schema::dropIfExists('branch_customer');
    }
};
