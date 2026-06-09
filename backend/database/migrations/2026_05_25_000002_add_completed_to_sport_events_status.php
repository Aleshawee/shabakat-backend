<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sport_events MODIFY COLUMN status ENUM('open', 'closed', 'settled', 'completed', 'cancelled') NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sport_events MODIFY COLUMN status ENUM('open', 'closed', 'settled') NOT NULL DEFAULT 'open'");
    }
};
