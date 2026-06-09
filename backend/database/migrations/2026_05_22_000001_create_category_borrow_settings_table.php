<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_borrow_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_borrowable')->default(false);
            $table->integer('max_borrow_amount')->default(0)->comment('أقصى مبلغ سلفة مسموح لهذه الفئة');
            $table->integer('min_points_threshold')->default(0)->comment('الحد الأدنى من النقاط للتأهل');
            $table->timestamps();
            $table->unique(['network_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_borrow_settings');
    }
};
