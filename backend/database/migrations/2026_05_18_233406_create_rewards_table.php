<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('اسم المكافأة');
            $table->text('description')->nullable();
            $table->integer('points_cost')->comment('عدد النقاط المطلوب للاستبدال');
            $table->decimal('card_value', 10, 2)->nullable()->comment('قيمة كرت النتيجة');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
