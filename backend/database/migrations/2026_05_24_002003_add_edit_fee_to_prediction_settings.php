<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prediction_settings', function (Blueprint $table) {
            $table->integer('edit_fee')->default(0)->after('allow_prediction_edit');
        });
    }

    public function down(): void
    {
        Schema::table('prediction_settings', function (Blueprint $table) {
            $table->dropColumn('edit_fee');
        });
    }
};
