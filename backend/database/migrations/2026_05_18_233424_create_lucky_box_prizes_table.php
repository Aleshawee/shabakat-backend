<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lucky_box_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('اسم الجائزة');
            $table->enum('type', ['points', 'reward_card', 'gift'])->default('points');
            $table->integer('value')->comment('قيمة الجائزة (نقاط أو id المكافأة)');
            $table->decimal('probability_weight', 8, 2)->default(1)->comment('الوزن الاحتمالي');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_box_prizes');
    }
};
