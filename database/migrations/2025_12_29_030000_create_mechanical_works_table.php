<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mechanical_works', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workshop_jobcard_id')->nullable();
            $table->unsignedBigInteger('service_type_id')->nullable();
            $table->unsignedBigInteger('repair_items_id')->nullable();
            $table->json('repair_items')->nullable();
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('workshop_jobcard_id')->references('id')->on('workshop_jobcards')->onDelete('cascade');
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('set null');
            // Intentionally not adding FK for repair_items_id because inventory table name may vary
        });
    }

    public function down()
    {
        Schema::dropIfExists('mechanical_works');
    }
};
