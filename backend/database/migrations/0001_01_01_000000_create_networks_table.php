<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('networks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم الشبكة');
            $table->string('slug')->unique()->comment('الاسم المختصر للـ subdomain');
            $table->string('domain')->nullable()->comment('الدومين المخصص');
            $table->string('owner_name')->nullable()->comment('اسم صاحب الشبكة');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0)->comment('نسبة عمولة المنصة (%)');
            $table->string('logo')->nullable();
            $table->json('theme_colors')->nullable()->comment('الألوان المخصصة');
            $table->enum('status', ['active', 'suspended', 'trial'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('networks');
    }
};
