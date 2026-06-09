<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lucky_box_plays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prize_id')->nullable()->constrained('lucky_box_prizes')->nullOnDelete();
            $table->integer('points_spent')->comment('النقاط اللي دفعها');
            $table->string('result')->nullable()->comment('وصف الجائزة اللي فاز بها');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_box_plays');
    }
};
