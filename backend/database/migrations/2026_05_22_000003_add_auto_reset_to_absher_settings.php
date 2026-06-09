<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absher_settings', function (Blueprint $table) {
            $table->boolean('auto_reset_points')->default(false)->after('activity_days');
            $table->date('last_reset_date')->nullable()->after('auto_reset_points');
        });
    }

    public function down(): void
    {
        Schema::table('absher_settings', function (Blueprint $table) {
            $table->dropColumn(['auto_reset_points', 'last_reset_date']);
        });
    }
};
