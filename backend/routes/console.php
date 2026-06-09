<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Tenant;
use App\Models\Setting;
use App\Models\User;
use App\Http\Controllers\Admin\RestrictionController;

Artisan::command('points:auto-reset', function () {
    $tenants = Tenant::where('status', 'active')->get();
    $resets = 0;

    foreach ($tenants as $tenant) {
        tenancy()->initialize($tenant);

        $enabled = Setting::where('key', 'auto_reset_enabled')
            ->where('group', 'restrictions')
            ->value('value');

        if (!$enabled) {
            tenancy()->end();
            continue;
        }

        $keepDebtors = Setting::where('key', 'reset_keep_debtors')
            ->where('group', 'restrictions')
            ->value('value');

        $query = User::query();
        if ($keepDebtors) {
            $query->where('points_balance', '>', 0);
        }

        $query->chunk(100, function ($users) use (&$resets) {
            foreach ($users as $user) {
                RestrictionController::applyReset($user);
                $resets++;
            }
        });

        Setting::updateOrCreate(
            ['key' => 'last_reset_date', 'group' => 'restrictions'],
            ['value' => now()->toDateString()]
        );

        tenancy()->end();
    }

    $this->info("تم تصفير {$resets} مستخدم");
})->purpose('تصفير أرصدة المستخدمين تلقائياً أول كل شهر');

Schedule::command('points:auto-reset')->monthlyOn(1, '00:00');
Schedule::command('sport-events:auto-close')->everyMinute();
