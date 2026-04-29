<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE work_orders MODIFY COLUMN status ENUM('open','quoted','in_progress','quality_check','ready','delivered','cancelled') NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE work_orders MODIFY COLUMN status ENUM('open','in_progress','quality_check','ready','delivered','cancelled') NOT NULL DEFAULT 'open'");
    }
};
