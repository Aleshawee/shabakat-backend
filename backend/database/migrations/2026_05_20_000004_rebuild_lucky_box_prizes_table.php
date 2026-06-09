<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lucky_box_plays', function (Blueprint $table) {
            $table->dropForeign(['prize_id']);
        });

        Schema::dropIfExists('lucky_box_prizes');

        Schema::create('lucky_box_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lucky_box_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('point');
            $table->string('value')->nullable();
            $table->integer('weight')->default(1);
            $table->timestamps();
        });

        Schema::table('lucky_box_plays', function (Blueprint $table) {
            $table->foreign('prize_id')->references('id')->on('lucky_box_prizes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lucky_box_plays', function (Blueprint $table) {
            $table->dropForeign(['prize_id']);
        });

        Schema::dropIfExists('lucky_box_prizes');

        Schema::create('lucky_box_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('points');
            $table->string('value');
            $table->decimal('probability_weight', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('lucky_box_plays', function (Blueprint $table) {
            $table->foreign('prize_id')->references('id')->on('lucky_box_prizes')->cascadeOnDelete();
        });
    }
};
