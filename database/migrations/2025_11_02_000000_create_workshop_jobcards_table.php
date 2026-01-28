<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('workshop_jobcards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jobcard_id');
            $table->unsignedBigInteger('material_id')->nullable(); // If selected from inventory
            $table->string('material_name')->nullable(); // For custom/manual entry
            $table->integer('quantity')->default(1);
            $table->json('additional_works')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('jobcard_id')->references('id')->on('job_cards')->onDelete('cascade');
            // Optionally, add foreign key for material_id if you have a materials/inventory table
        });
    }

    public function down()
    {
        Schema::dropIfExists('workshop_jobcards');
    }
};
