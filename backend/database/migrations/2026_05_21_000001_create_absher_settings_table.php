<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absher_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->foreignId('loan_reward_id')->nullable()->constrained('rewards')->nullOnDelete();
            $table->integer('point_cost')->default(0);
            $table->integer('min_points_threshold')->nullable();
            $table->string('start_time', 5)->nullable();
            $table->string('end_time', 5)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absher_settings');
    }
};
