<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vehicle_items')) {
            return;
        }

        Schema::table('vehicle_items', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_items', 'job_card_id')) {
                $table->foreignId('job_card_id')->nullable()->constrained('job_cards')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('vehicle_items')) {
            return;
        }

        Schema::table('vehicle_items', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_items', 'job_card_id')) {
                $table->dropForeign(['job_card_id']);
                $table->dropColumn('job_card_id');
            }
        });
    }
};
