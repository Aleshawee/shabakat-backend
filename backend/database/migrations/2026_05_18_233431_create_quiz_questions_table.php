<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contest_id')->constrained('quiz_contests')->cascadeOnDelete();
            $table->text('question');
            $table->json('options')->comment('مصفوفة الخيارات');
            $table->string('correct_answer');
            $table->integer('points')->default(0)->comment('نقاط الإجابة الصحيحة');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
