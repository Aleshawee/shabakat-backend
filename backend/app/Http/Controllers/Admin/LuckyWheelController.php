<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LuckyWheel;
use App\Models\LuckyWheelPlay;
use App\Models\Category;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LuckyWheelController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        $query = LuckyWheel::with('prizes');

        return response()->json([
            'wheels' => $query->latest()->get(),
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $admin = $request->user();

        $data = $request->validate([
            'wheels' => 'required|array|max:4',
            'wheels.*.id' => 'nullable|integer|exists:lucky_wheels,id',
            'wheels.*.name' => 'required|string|max:255',
            'wheels.*.spin_mode' => 'required|in:none,daily_then_points,daily_free_only',
            'wheels.*.point_cost' => 'nullable|integer|min:0',
            'wheels.*.daily_limit' => 'nullable|integer|min:0',
            'wheels.*.color' => 'nullable|string|max:7',
            'wheels.*.is_active' => 'boolean',
            'wheels.*.prizes' => 'array',
            'wheels.*.prizes.*.id' => 'nullable|integer|exists:lucky_wheel_prizes,id',
            'wheels.*.prizes.*.name' => 'required|string|max:255',
            'wheels.*.prizes.*.type' => 'required|in:point,card,nothng',
            'wheels.*.prizes.*.value' => 'nullable|string|max:255',
            'wheels.*.prizes.*.weight' => 'required|integer|min:1',
            'wheels.*.prizes.*.color' => 'nullable|string|max:7',
        ]);

        $savedWheelIds = [];

        foreach ($data['wheels'] as $wheelData) {
            $wheel = LuckyWheel::updateOrCreate(
                ['id' => $wheelData['id'] ?? null],
                [
                    'name' => $wheelData['name'],
                    'spin_mode' => $wheelData['spin_mode'],
                    'point_cost' => $wheelData['point_cost'] ?? 0,
                    'daily_limit' => $wheelData['daily_limit'] ?? 0,
                    'color' => $wheelData['color'] ?? '#6366f1',
                    'is_active' => $wheelData['is_active'] ?? true,
                ]
            );
            $savedWheelIds[] = $wheel->id;

            $savedPrizeIds = [];
            foreach ($wheelData['prizes'] ?? [] as $prizeData) {
                $prize = $wheel->prizes()->updateOrCreate(
                    ['id' => $prizeData['id'] ?? null],
                    [
                        'name' => $prizeData['name'],
                        'type' => $prizeData['type'],
                        'value' => $prizeData['value'] ?? '0',
                        'weight' => $prizeData['weight'],
                        'color' => $prizeData['color'] ?? '#6366f1',
                    ]
                );
                $savedPrizeIds[] = $prize->id;
            }

            $wheel->prizes()->whereNotIn('id', $savedPrizeIds)->delete();
        }

        LuckyWheel::whereNotIn('id', $savedWheelIds)->delete();

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'update',
            'target_type' => 'lucky_wheel',
            'details' => 'تحديث إعدادات عجلة الحظ',
        ]);

        return $this->index($request);
    }

    public function resetFreeSpins(Request $request, $id)
    {
        $admin = $request->user();
        $wheel = LuckyWheel::findOrFail($id);

        $deleted = LuckyWheelPlay::where('lucky_wheel_id', $wheel->id)
            ->whereDate('created_at', today())
            ->delete();

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'reset_free_spins',
            'target_type' => 'lucky_wheel',
            'target_id' => $wheel->id,
            'details' => "تصفير اللفات المجانية للعجلة: {$wheel->name} — {$deleted} لفة",
        ]);

        return response()->json([
            'message' => "تم تصفير {$deleted} لفة مجانية",
            'deleted' => $deleted,
        ]);
    }

    public function resetFreeSpinsForUser(Request $request, $id)
    {
        $admin = $request->user();
        $validated = $request->validate(['phone' => 'required|string']);
        $phone = preg_replace('/^\+?967/', '', $validated['phone']);

        $wheel = LuckyWheel::findOrFail($id);

        $user = User::whereRaw("REPLACE(phone, '+967', '') = ?", [$phone])
            ->orWhere('phone', $phone)
            ->orWhere('phone', '+967' . $phone)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $deleted = LuckyWheelPlay::where('lucky_wheel_id', $wheel->id)
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->delete();

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'reset_free_spins_user',
            'target_type' => 'user',
            'target_id' => $user->id,
            'details' => "تصفير اللفات المجانية للمستخدم: {$user->name} ({$user->phone}) في العجلة: {$wheel->name}",
        ]);

        return response()->json([
            'message' => "تم تصفير {$deleted} لفة للمستخدم {$user->name}",
            'deleted' => $deleted,
        ]);
    }
}
