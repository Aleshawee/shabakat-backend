<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lucky_wheels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('spin_mode')->default('none')->comment('none, daily_then_points, daily_free_only');
            $table->integer('point_cost')->default(0);
            $table->integer('daily_limit')->default(0)->comment('0 = غير محدود');
            $table->string('color', 7)->default('#6366f1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('lucky_wheel_plays', function (Blueprint $table) {
            $table->dropForeign(['segment_id']);
        });

        Schema::dropIfExists('lucky_wheel_segments');

        Schema::create('lucky_wheel_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lucky_wheel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('point')->comment('point, card, nothng');
            $table->string('value')->nullable();
            $table->integer('weight')->default(1);
            $table->string('color', 7)->default('#6366f1');
            $table->timestamps();
        });

        Schema::table('lucky_wheel_plays', function (Blueprint $table) {
            $table->foreignId('lucky_wheel_id')->nullable()->constrained()->cascadeOnDelete()->after('user_id');
            $table->renameColumn('segment_id', 'prize_id');
            $table->foreign('prize_id')->references('id')->on('lucky_wheel_prizes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lucky_wheel_plays', function (Blueprint $table) {
            $table->dropForeign(['prize_id']);
            $table->dropColumn('lucky_wheel_id');
            $table->renameColumn('prize_id', 'segment_id');
        });

        Schema::dropIfExists('lucky_wheel_prizes');

        Schema::create('lucky_wheel_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['points', 'reward_card', 'gift', 'try_again'])->default('points');
            $table->integer('value')->nullable();
            $table->string('color', 7)->default('#6366f1');
            $table->decimal('probability_weight', 8, 2)->default(1);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('lucky_wheel_plays', function (Blueprint $table) {
            $table->foreign('segment_id')->references('id')->on('lucky_wheel_segments')->nullOnDelete();
        });

        Schema::dropIfExists('lucky_wheels');
    }
};
