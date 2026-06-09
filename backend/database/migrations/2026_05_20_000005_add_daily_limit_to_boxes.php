<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lucky_boxes', function (Blueprint $table) {
            $table->integer('daily_limit')->default(0)->after('cost')->comment('0 = غير محدود');
        });

        Schema::table('lucky_box_plays', function (Blueprint $table) {
            $table->foreignId('lucky_box_id')->nullable()->constrained()->cascadeOnDelete()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('lucky_boxes', function (Blueprint $table) {
            $table->dropColumn('daily_limit');
        });

        Schema::table('lucky_box_plays', function (Blueprint $table) {
            $table->dropForeign(['lucky_box_id']);
            $table->dropColumn('lucky_box_id');
        });
    }
};
