<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('admins', 'tenant_id')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('tenant_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('admins', 'tenant_id')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
