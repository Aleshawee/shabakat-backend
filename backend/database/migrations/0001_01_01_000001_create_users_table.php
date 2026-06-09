<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('phone')->unique()->comment('رقم الهاتف — طريقة التسجيل');
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('device_id')->nullable()->comment('معرّف الجهاز لكشف الاحتيال');
            $table->integer('points_balance')->default(0)->comment('رصيد النقاط الحالي');
            $table->decimal('wallet_balance', 10, 2)->default(0)->comment('رصيد المحفظة المالية');
            $table->enum('status', ['active', 'suspended', 'banned'])->default('active');
            $table->timestamp('last_active_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
