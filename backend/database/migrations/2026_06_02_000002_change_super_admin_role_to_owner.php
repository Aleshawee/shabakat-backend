<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE admins MODIFY COLUMN role VARCHAR(20) DEFAULT 'admin'");
        DB::table('admins')->where('role', 'super_admin')->update(['role' => 'owner']);
        DB::statement("ALTER TABLE admins MODIFY COLUMN role ENUM('owner', 'admin') DEFAULT 'admin'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE admins MODIFY COLUMN role VARCHAR(20) DEFAULT 'admin'");
        DB::table('admins')->where('role', 'owner')->update(['role' => 'super_admin']);
        DB::statement("ALTER TABLE admins MODIFY COLUMN role ENUM('super_admin', 'admin') DEFAULT 'admin'");
    }
};
