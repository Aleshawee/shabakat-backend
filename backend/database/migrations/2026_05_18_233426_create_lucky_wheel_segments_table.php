<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lucky_wheel_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('اسم الجائزة');
            $table->enum('type', ['points', 'reward_card', 'gift', 'try_again'])->default('points');
            $table->integer('value')->nullable()->comment('القيمة');
            $table->string('color', 7)->default('#6366f1')->comment('لون الشريحة');
            $table->decimal('probability_weight', 8, 2)->default(1);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_wheel_segments');
    }
};
