<?php
// bootstrap script: seeds central data and creates initial tenant
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Create owner admin if not exists
use App\Models\Admin;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

$owner = Admin::on('mysql')->where('email', 'admin@shabakat.com')->first();
if (!$owner) {
    Admin::on('mysql')->create([
        'name' => 'المطور',
        'email' => 'admin@shabakat.com',
        'password' => bcrypt('password'),
        'role' => 'owner',
    ]);
    echo "Owner admin created.\n";
} else {
    echo "Owner admin exists.\n";
}

// 2. Create sama tenant if not exists
$tenant = Tenant::on('mysql')->find('sama');
if (!$tenant) {
    $tenant = Tenant::on('mysql')->create([
        'id' => 'sama',
        'name' => 'الشبكة التجريبية',
        'slug' => 'sama',
        'owner_name' => 'صاحب الشبكة',
        'phone' => '777000000',
        'status' => 'active',
    ]);
    echo "Tenant 'sama' created.\n";
} else {
    echo "Tenant 'sama' exists.\n";
}

// 3. Create domain for sama
$domain = $tenant->domains()->where('domain', 'sama.shabakat-backend.onrender.com')->first();
if (!$domain) {
    $tenant->domains()->create(['domain' => 'sama.shabakat-backend.onrender.com']);
    echo "Domain created.\n";
} else {
    echo "Domain exists.\n";
}

// 4. Create network admin if not exists
$netAdmin = Admin::on('mysql')->where('email', 'network@demo.com')->first();
if (!$netAdmin) {
    Admin::on('mysql')->create([
        'name' => 'مدير الشبكة',
        'email' => 'network@demo.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'tenant_id' => 'sama',
    ]);
    echo "Network admin created.\n";
} else {
    echo "Network admin exists.\n";
}

// 5. Run tenant migrations explicitly
echo "Running tenant migrations...\n";
$tenant = Tenant::on('mysql')->find('sama');
if ($tenant) {
    try {
        $tenant->run(function () {
            Artisan::call('migrate', ['--force' => true, '--path' => database_path('migrations/tenant'), '--realpath' => true]);
            echo Artisan::output() . "\n";
        });
        echo "Tenant migrations done.\n";
    } catch (\Exception $e) {
        echo "Tenant migration error: " . $e->getMessage() . "\n";
    }
}

// 6. Create test user in sama tenant
tenancy()->end(); // ensure central context
echo "Step 6: Creating test user...\n";
try {
    $sama = Tenant::on('mysql')->find('sama');
    if ($sama) {
        echo "  Tenant found.\n";
        tenancy()->initialize($sama);
        echo "  Tenancy initialized.\n";
        echo "  DB: " . DB::connection()->getDatabaseName() . "\n";
        
        // Check if users table exists
        $hasTable = Schema::hasTable('users');
        echo "  Users table exists: " . ($hasTable ? 'yes' : 'no') . "\n";
        
        $user = \App\Models\User::where('phone', '597100889')->first();
        echo "  User 9-digit query: " . ($user ? 'found' : 'not found') . "\n";
        
        // Migrate old 10-digit phone to 9-digit format (matches 967 prefix input)
        if (!$user) {
            $oldUser = \App\Models\User::where('phone', '0597100889')->first();
            if ($oldUser) {
                $oldUser->update(['phone' => '597100889']);
                $user = $oldUser;
                echo "  Migrated phone from 0597100889 to 597100889\n";
            }
        }
        
        if (!$user) {
            try {
                $created = \App\Models\User::create([
                    'name' => 'USOO3USU+U^',
                    'phone' => '597100889',
                    'password' => bcrypt('password'),
                ]);
                echo "  User created: " . ($created ? 'yes (id=' . $created->id . ')' : 'no') . "\n";
                
                // Also set verified directly on the model
                $created->phone_verified_at = now();
                $created->last_active_at = now();
                $created->save();
                echo "  User updated with timestamps.\n";
            } catch (\Exception $e2) {
                echo "  User create error: " . $e2->getMessage() . "\n";
            }
        }
        tenancy()->end();
        echo "  Tenancy ended.\n";
    } else {
        echo "  Tenant sama not found.\n";
    }
} catch (\Exception $e) {
    echo "Tenant DB error: " . $e->getMessage() . "\n";
}

echo "Bootstrap complete.\n";
