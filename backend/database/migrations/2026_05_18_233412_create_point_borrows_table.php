<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_borrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount')->comment('عدد النقاط المقترضة');
            $table->integer('fee')->default(0)->comment('رسوم الخدمة بالنقاط');
            $table->integer('total_debt')->comment('إجمالي الدين = amount + fee');
            $table->integer('repaid_amount')->default(0)->comment('ما تم سداده');
            $table->enum('status', ['active', 'repaid', 'defaulted'])->default('active');
            $table->timestamp('repaid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_borrows');
    }
};
