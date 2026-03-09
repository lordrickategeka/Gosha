<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmploymentDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('employment_type');
            $table->string('job_title');
            $table->string('department');
            $table->date('hired_at');
            $table->date('terminated_at')->nullable();
            $table->string('termination_reason')->nullable();
            $table->date('probation_end_at')->nullable();
            $table->string('skill_level');
            $table->json('specializations')->nullable();
            $table->json('certifications')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employment_details');
    }
}
