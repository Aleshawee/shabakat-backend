<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->integer('max_active_events')->default(5);
            $table->integer('min_time_before_deadline')->default(60);
            $table->boolean('allow_prediction_edit')->default(false);
            $table->boolean('auto_distribute_rewards')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_settings');
    }
};
