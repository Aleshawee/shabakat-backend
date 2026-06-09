<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sport_events', function (Blueprint $table) {
            $table->integer('home_score')->nullable()->after('winner');
            $table->integer('away_score')->nullable()->after('home_score');
        });
    }

    public function down(): void
    {
        Schema::table('sport_events', function (Blueprint $table) {
            $table->dropColumn(['home_score', 'away_score']);
        });
    }
};
