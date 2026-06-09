<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->comment('earn, spend, transfer_in, transfer_out, borrow, repay, wheel, lucky_box, quiz, prediction, admin_adjust');
            $table->integer('amount')->comment('(+) كسب، (-) صرف');
            $table->integer('balance_after')->comment('الرصيد بعد العملية');
            $table->nullableMorphs('reference'); /* reference_type + reference_id */
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
