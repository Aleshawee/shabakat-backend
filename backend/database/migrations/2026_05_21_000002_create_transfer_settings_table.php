<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->integer('min_transfer_amount')->default(10);
            $table->integer('max_transfer_amount')->default(1000);
            $table->decimal('transfer_fee_percent', 5, 1)->default(0.0);
            $table->integer('min_balance_required')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_settings');
    }
};
