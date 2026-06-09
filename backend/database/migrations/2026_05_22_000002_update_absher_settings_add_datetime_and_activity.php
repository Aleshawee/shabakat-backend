<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absher_settings', function (Blueprint $table) {
            $table->renameColumn('start_time', 'starts_at');
            $table->renameColumn('end_time', 'ends_at');
        });

        Schema::table('absher_settings', function (Blueprint $table) {
            $table->dateTime('starts_at')->nullable()->change();
            $table->dateTime('ends_at')->nullable()->change();
            $table->boolean('require_recent_activity')->default(false)->after('ends_at');
            $table->integer('activity_days')->default(0)->after('require_recent_activity');
        });
    }

    public function down(): void
    {
        Schema::table('absher_settings', function (Blueprint $table) {
            $table->dropColumn(['require_recent_activity', 'activity_days']);
        });

        Schema::table('absher_settings', function (Blueprint $table) {
            $table->string('starts_at', 5)->nullable()->change();
            $table->string('ends_at', 5)->nullable()->change();
            $table->renameColumn('starts_at', 'start_time');
            $table->renameColumn('ends_at', 'end_time');
        });
    }
};
