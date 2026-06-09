<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::create([
            'name' => 'الشبكة التجريبية',
            'slug' => 'sama',
            'owner_name' => 'صاحب الشبكة',
            'phone' => '777000000',
            'status' => 'active',
        ]);

        $tenant->domains()->create(['domain' => 'sama.' . config('app.domain')]);

        Admin::create([
            'name' => 'المطور',
            'email' => 'admin@shabakat.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        tenancy()->initialize($tenant);

        Admin::create([
            'name' => 'مدير الشبكة',
            'email' => 'network@demo.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        tenancy()->end();
    }
}
