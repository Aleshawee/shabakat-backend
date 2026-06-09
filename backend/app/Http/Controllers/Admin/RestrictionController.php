<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\PointTransaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class RestrictionController extends Controller
{
    public static function applyReset(User $user, $networkId = null, $type = 'auto_reset')
    {
        $resetType = Setting::where('key', 'reset_type')->where('group', 'restrictions')->value('value') ?? 'zero';
        $resetValue = (int) (Setting::where('key', 'reset_value')->where('group', 'restrictions')->value('value') ?? 0);

        $oldBalance = $user->points_balance;

        match ($resetType) {
            'percentage' => $newBalance = max(0, (int) round($oldBalance * (100 - $resetValue) / 100)),
            'fixed' => $newBalance = max(0, $oldBalance - $resetValue),
            default => $newBalance = 0,
        };

        $user->update(['points_balance' => $newBalance]);

        $note = match ($type) {
            'admin_reset' => match ($resetType) {
                'percentage' => "خصم {$resetValue}% شامل",
                'fixed' => "خصم {$resetValue} نقطة شامل",
                default => 'تصفير شامل للنقاط',
            },
            default => match ($resetType) {
                'percentage' => "خصم {$resetValue}% تلقائي أول الشهر",
                'fixed' => "خصم {$resetValue} نقطة تلقائي أول الشهر",
                default => 'تصفير تلقائي أول الشهر',
            },
        };

        PointTransaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $newBalance - $oldBalance,
            'balance_after' => $newBalance,
            'note' => $note,
        ]);
    }

    public static function runAutoResetIfNeeded($networkId = null)
    {
        $enabled = Setting::where('key', 'auto_reset_enabled')
            ->where('group', 'restrictions')
            ->value('value');

        if (!$enabled) return;

        $lastReset = Setting::where('key', 'last_reset_date')
            ->where('group', 'restrictions')
            ->value('value');

        $firstOfMonth = now()->startOfMonth()->toDateString();
        if ($lastReset && $lastReset >= $firstOfMonth) return;

        $keepDebtors = Setting::where('key', 'reset_keep_debtors')
            ->where('group', 'restrictions')
            ->value('value');

        $query = User::query();
        if ($keepDebtors) $query->where('points_balance', '>', 0);

        $resets = 0;
        $query->chunk(100, function ($users) use (&$resets) {
            foreach ($users as $user) {
                static::applyReset($user);
                $resets++;
            }
        });

        Setting::updateOrCreate(
            ['key' => 'last_reset_date', 'group' => 'restrictions'],
            ['value' => now()->toDateString()]
        );

        if ($resets) {
            ActivityLog::create([
                'admin_id' => 0,
                'action' => 'auto_reset_points',
                'target_type' => 'network',
                'details' => "تصفير تلقائي لـ {$resets} مستخدم",
            ]);
        }
    }

    public function show(Request $request)
    {
        static::runAutoResetIfNeeded();

        $settings = Setting::where('group', 'restrictions')
            ->get()
            ->pluck('value', 'key');

        return response()->json([
            'daily_card_redeem_limit' => (int) ($settings['daily_card_redeem_limit'] ?? 0),
            'daily_card_exceed_action' => $settings['daily_card_exceed_action'] ?? 'block',
            'auto_reset_enabled' => (bool) ($settings['auto_reset_enabled'] ?? false),
            'reset_type' => $settings['reset_type'] ?? 'zero',
            'reset_value' => (int) ($settings['reset_value'] ?? 0),
            'reset_keep_debtors' => (bool) ($settings['reset_keep_debtors'] ?? true),
            'last_reset_date' => $settings['last_reset_date'] ?? null,
            'reset_notification_enabled' => (bool) ($settings['reset_notification_enabled'] ?? false),
            'reset_notification_message' => $settings['reset_notification_message'] ?? '',
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'daily_card_redeem_limit' => 'integer|min:0',
            'daily_card_exceed_action' => 'in:block,suspend',
            'auto_reset_enabled' => 'boolean',
            'reset_type' => 'in:zero,percentage,fixed',
            'reset_value' => 'required_if:reset_type,percentage,fixed|integer|min:0',
            'reset_keep_debtors' => 'boolean',
            'reset_notification_enabled' => 'boolean',
            'reset_notification_message' => 'nullable|string|max:500',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'restrictions'],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
            );
        }

        return response()->json(['message' => 'تم حفظ القيود']);
    }

    public function resetUser(Request $request, $id)
    {
        $admin = $request->user();
        $user = User::findOrFail($id);

        $oldBalance = $user->points_balance;
        $user->update(['points_balance' => 0]);

        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'admin_reset',
            'amount' => -$oldBalance,
            'balance_after' => 0,
            'note' => 'تصفير رصيد النقاط بواسطة الإدارة',
        ]);

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'reset_points',
            'target_type' => 'user',
            'target_id' => $user->id,
            'details' => "تصفير رصيد المستخدم {$user->name} ({$user->phone}) من {$oldBalance} إلى 0",
        ]);

        return response()->json(['message' => "تم تصفير رصيد {$user->name}"]);
    }

    public function resetByPhone(Request $request)
    {
        $admin = $request->user();
        $validated = $request->validate(['phone' => 'required|string']);

        $phone = preg_replace('/^\+?967/', '', $validated['phone']);
        $user = User::where(function ($q) use ($phone) {
                $q->where('phone', $phone)->orWhere('phone', '+967' . $phone);
            })->first();

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $oldBalance = $user->points_balance;
        $user->update(['points_balance' => 0]);

        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'admin_reset',
            'amount' => -$oldBalance,
            'balance_after' => 0,
            'note' => 'تصفير رصيد النقاط بواسطة الإدارة',
        ]);

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'reset_points',
            'target_type' => 'user',
            'target_id' => $user->id,
            'details' => "تصفير رصيد المستخدم {$user->name} ({$user->phone}) من {$oldBalance} إلى 0",
        ]);

        return response()->json(['message' => "تم تصفير رصيد {$user->name}"]);
    }

    public function resetAll(Request $request)
    {
        $admin = $request->user();
        $keepDebtors = $request->boolean('keep_debtors', true);

        $query = User::query();
        if ($keepDebtors) {
            $query->where('points_balance', '>', 0);
        }

        $count = 0;
        $query->chunk(100, function ($users) use ($admin, &$count) {
            foreach ($users as $user) {
                static::applyReset($user, null, 'admin_reset');
                $count++;
            }
        });

        Setting::updateOrCreate(
            ['key' => 'last_reset_date', 'group' => 'restrictions'],
            ['value' => now()->toDateString()]
        );

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'reset_all_points',
            'target_type' => 'network',
            'details' => "تصفير نقاط {$count} مستخدم" . ($keepDebtors ? ' (مع إبقاء المديونين)' : ''),
        ]);

        return response()->json(['message' => "تم تصفير نقاط {$count} مستخدم"]);
    }
}
