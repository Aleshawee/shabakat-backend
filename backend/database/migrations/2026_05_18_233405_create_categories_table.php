<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('اسم الفئة: 100 ريال، 200 ريال...');
            $table->decimal('price', 10, 2)->comment('سعر الكرت بالريال');
            $table->integer('points')->comment('النقاط اللي ياخذها المشترك');
            $table->boolean('is_active')->default(true)->comment('ظهور للعملاء أو إخفاء');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
