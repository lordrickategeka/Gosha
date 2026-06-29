<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['inspection', 'emissions', 'insurance', 'permit']);
            $table->date('expiry_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index(['type', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_records');
    }
};
