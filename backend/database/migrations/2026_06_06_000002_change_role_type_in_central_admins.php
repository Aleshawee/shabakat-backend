<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('central')->hasColumn('admins', 'role')) {
            Schema::connection('central')->table('admins', function (Blueprint $table) {
                $table->string('role')->change();
            });
        }
    }

    public function down(): void
    {
    }
};
