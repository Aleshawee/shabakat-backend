<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sport_events', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('event_type')->default('match')->after('description');
            $table->string('prediction_type')->default('winner')->after('event_type');
            $table->boolean('allow_draw')->default(true)->after('prediction_type');
            $table->string('option_a_image')->nullable()->after('away_team');
            $table->string('option_b_image')->nullable()->after('option_a_image');
            $table->dateTime('prediction_deadline')->nullable()->after('match_date');
            $table->integer('reward_per_winner')->default(0)->after('entry_fee');
        });
    }

    public function down(): void
    {
        Schema::table('sport_events', function (Blueprint $table) {
            $table->dropColumn(['description', 'event_type', 'prediction_type', 'allow_draw', 'option_a_image', 'option_b_image', 'prediction_deadline', 'reward_per_winner']);
        });
    }
};
