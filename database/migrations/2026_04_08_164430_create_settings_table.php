<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained()->cascadeOnDelete(); // null = platform settings
            $table->string('group')->default('general'); // general, invoice, notification, etc.
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->timestamps();

            $table->unique(['vendor_id', 'group', 'key']);
            $table->index(['vendor_id', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
