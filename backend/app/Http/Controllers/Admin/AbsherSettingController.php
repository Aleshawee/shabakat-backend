<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsherSetting;
use App\Models\Category;
use App\Models\CategoryBorrowSetting;
use App\Models\PointBorrow;
use Illuminate\Http\Request;

class AbsherSettingController extends Controller
{
    public function show(Request $request)
    {
        $admin = $request->user();

        $setting = AbsherSetting::firstOrCreate(
            [],
            ['is_enabled' => false, 'point_cost' => 0]
        );

        $categories = Category::where('is_active', true)
            ->with(['rewards' => fn($q) => $q->where('is_active', true)])
            ->get()
            ->map(function ($cat) {
                $bs = CategoryBorrowSetting::firstOrCreate(
                    ['category_id' => $cat->id],
                    ['is_borrowable' => false, 'max_borrow_amount' => 0, 'min_points_threshold' => 0]
                );
                $reward = $cat->rewards->sortBy('points_cost')->first();
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'price' => $cat->price,
                    'points' => $cat->points,
                    'reward' => $reward ? ['id' => $reward->id, 'name' => $reward->name, 'points_cost' => $reward->points_cost] : null,
                    'borrow_setting' => [
                        'id' => $bs->id,
                        'is_borrowable' => $bs->is_borrowable,
                        'max_borrow_amount' => $bs->max_borrow_amount,
                        'min_points_threshold' => $bs->min_points_threshold,
                    ],
                ];
            });

        $stats = [
            'total_borrowers' => PointBorrow::distinct('user_id')->count('user_id'),
            'total_loans' => PointBorrow::count(),
            'total_amount' => (int) PointBorrow::sum('amount'),
            'total_debt' => (int) PointBorrow::sum('total_debt'),
            'active_loans' => PointBorrow::where('status', 'active')->count(),
            'repaid_loans' => PointBorrow::where('status', 'repaid')->count(),
            'defaulted_loans' => PointBorrow::where('status', 'defaulted')->count(),
        ];

        return response()->json([
            'setting' => $setting,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request)
    {
        $admin = $request->user();
        $data = $request->validate([
            'is_enabled' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'require_recent_activity' => 'boolean',
            'activity_days' => 'integer|min:0',
        ]);

        $setting = AbsherSetting::firstOrCreate([]);
        $setting->update($data);

        // حفظ إعدادات الفئات
        if ($request->has('category_settings') && is_array($request->category_settings)) {
            foreach ($request->category_settings as $catData) {
                if (!isset($catData['category_id'])) continue;
                $bs = CategoryBorrowSetting::firstOrCreate(
                    ['category_id' => $catData['category_id']],
                    ['is_borrowable' => false, 'max_borrow_amount' => 0, 'min_points_threshold' => 0]
                );
                $bs->update([
                    'is_borrowable' => $catData['is_borrowable'] ?? false,
                    'max_borrow_amount' => $catData['max_borrow_amount'] ?? 0,
                    'min_points_threshold' => $catData['min_points_threshold'] ?? 0,
                ]);
            }
        }

        return response()->json(['message' => 'تم الحفظ']);
    }
}
