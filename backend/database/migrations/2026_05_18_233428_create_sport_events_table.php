<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->string('title')->comment('وصف المباراة');
            $table->string('home_team')->nullable();
            $table->string('away_team')->nullable();
            $table->datetime('match_date');
            $table->integer('entry_fee')->default(0)->comment('رسم الاشتراك (نقاط)');
            $table->enum('status', ['open', 'closed', 'settled'])->default('open');
            $table->string('winner')->nullable()->comment('النتيجة النهائية');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_events');
    }
};
