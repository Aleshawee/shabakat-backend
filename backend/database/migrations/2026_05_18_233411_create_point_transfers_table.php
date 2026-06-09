<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->integer('amount')->comment('المبلغ بعد خصم العمولة');
            $table->integer('fee')->default(0)->comment('العمولة (نقاط)');
            $table->integer('gross_amount')->comment('المبلغ قبل خصم العمولة');
            $table->enum('status', ['completed', 'failed'])->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transfers');
    }
};
