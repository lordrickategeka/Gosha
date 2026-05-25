<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // ISO 4217 code
            $table->string('name');
            $table->string('symbol', 10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default currencies
        DB::table('currencies')->insert([
            ['code' => 'UGX', 'name' => 'Ugandan Shilling', 'symbol' => 'UGX', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
