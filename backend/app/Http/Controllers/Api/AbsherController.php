<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsherSetting;
use App\Models\Category;
use App\Models\CategoryBorrowSetting;
use App\Models\PointBorrow;
use App\Models\PointTransaction;
use App\Models\RewardCard;
use Illuminate\Http\Request;

class AbsherController extends Controller
{
    public function settings(Request $request)
    {
        $user = $request->user();
        $setting = AbsherSetting::first();

        if (!$setting || !$setting->is_enabled) {
            return response()->json(['enabled' => false, 'message' => 'الميزة غير مفعلة']);
        }

        $now = now();
        if ($setting->starts_at && $setting->ends_at) {
            $within = $now >= $setting->starts_at && $now <= $setting->ends_at;
            if (!$within) {
                return response()->json(['enabled' => false, 'message' => 'الخدمة غير متاحة حالياً', 'starts_at' => $setting->starts_at, 'ends_at' => $setting->ends_at]);
            }
        }

        if ($setting->require_recent_activity && $setting->activity_days > 0) {
            $cutoff = now()->subDays($setting->activity_days);
            if (!$user->last_active_at || $user->last_active_at < $cutoff) {
                return response()->json(['enabled' => false, 'message' => 'غير مؤهل للسلفة — يجب أن يكون آخر نشاط خلال ' . $setting->activity_days . ' أيام ماضية']);
            }
        }

        $categories = Category::where('is_active', true)
            ->with(['rewards' => fn($q) => $q->where('is_active', true), 'borrowSetting'])
            ->get()
            ->filter(fn($cat) => $cat->borrowSetting && $cat->borrowSetting->is_borrowable && $cat->rewards->isNotEmpty())
            ->values()
            ->map(function ($cat) use ($user) {
                $reward = $cat->rewards->sortBy('points_cost')->first();
                $bs = $cat->borrowSetting;
                $needed = max(0, $reward->points_cost - $user->points_balance);
                $eligible = $user->points_balance >= $bs->min_points_threshold
                    && $user->points_balance < $reward->points_cost
                    && $needed <= $bs->max_borrow_amount
                    && $needed > 0;
                $reason = null;
                if (!$eligible) {
                    if ($user->points_balance < $bs->min_points_threshold)
                        $reason = 'رصيدك أقل من الحد الأدنى المطلوب (' . $bs->min_points_threshold . ' نقطة)';
                    elseif ($user->points_balance >= $reward->points_cost)
                        $reason = 'رصيدك كافٍ لشراء هذه المكافأة';
                    elseif ($needed > $bs->max_borrow_amount)
                        $reason = 'المبلغ المطلوب (' . $needed . ') يتجاوز الحد الأقصى للسلفة (' . $bs->max_borrow_amount . ')';
                    elseif ($needed <= 0)
                        $reason = 'رصيدك كافٍ بالفعل';
                    else
                        $reason = 'غير مؤهل للسلفة';
                }
                return [
                    'category_id' => $cat->id,
                    'category_name' => $cat->name,
                    'category_price' => $cat->price,
                    'reward' => [
                        'id' => $reward->id,
                        'name' => $reward->name,
                        'points_cost' => $reward->points_cost,
                    ],
                    'borrow_setting' => [
                        'max_borrow_amount' => $bs->max_borrow_amount,
                        'min_points_threshold' => $bs->min_points_threshold,
                    ],
                    'user_balance' => $user->points_balance,
                    'needed_amount' => $needed,
                    'is_eligible' => $eligible,
                    'ineligible_reason' => $reason,
                ];
            });

        return response()->json([
            'enabled' => true,
            'starts_at' => $setting->starts_at,
            'ends_at' => $setting->ends_at,
            'require_recent_activity' => $setting->require_recent_activity,
            'activity_days' => $setting->activity_days,
            'categories' => $categories,
        ]);
    }

    public function request(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate(['category_id' => 'required|exists:categories,id']);

        $setting = AbsherSetting::first();
        if (!$setting || !$setting->is_enabled) {
            return response()->json(['message' => 'الميزة غير مفعلة'], 403);
        }

        $now = now();
        if ($setting->starts_at && $setting->ends_at) {
            $within = $now >= $setting->starts_at && $now <= $setting->ends_at;
            if (!$within) {
                return response()->json(['message' => 'الخدمة غير متاحة حالياً'], 403);
            }
        }

        if ($setting->require_recent_activity && $setting->activity_days > 0) {
            $cutoff = now()->subDays($setting->activity_days);
            if (!$user->last_active_at || $user->last_active_at < $cutoff) {
                return response()->json(['message' => 'غير مؤهل للسلفة — يجب أن يكون آخر نشاط خلال ' . $setting->activity_days . ' أيام ماضية'], 403);
            }
        }

        $category = Category::where('is_active', true)
            ->where('id', $validated['category_id'])
            ->with(['rewards' => fn($q) => $q->where('is_active', true), 'borrowSetting'])
            ->first();

        if (!$category) {
            return response()->json(['message' => 'الفئة غير موجودة'], 404);
        }

        $bs = $category->borrowSetting;
        if (!$bs || !$bs->is_borrowable) {
            return response()->json(['message' => 'السلفة غير متاحة لهذه الفئة'], 403);
        }

        $reward = $category->rewards->sortBy('points_cost')->first();
        if (!$reward) {
            return response()->json(['message' => 'لا توجد مكافأة متاحة لهذه الفئة'], 500);
        }

        $needed = $reward->points_cost - $user->points_balance;
        if ($needed <= 0) {
            return response()->json(['message' => 'رصيدك كافٍ — يمكنك استبدال المكافأة مباشرة من صفحة المكافآت'], 403);
        }
        if ($user->points_balance < $bs->min_points_threshold) {
            return response()->json(['message' => 'رصيدك أقل من الحد الأدنى المطلوب (' . $bs->min_points_threshold . ' نقطة)'], 403);
        }
        if ($needed > $bs->max_borrow_amount) {
            return response()->json(['message' => 'المبلغ المطلوب (' . $needed . ') يتجاوز الحد الأقصى للسلفة (' . $bs->max_borrow_amount . ')'], 403);
        }

        // التحقق من وجود كرت متاح
        $card = RewardCard::where('reward_id', $reward->id)
            ->where('status', 'available')
            ->first();

        if (!$card) {
            return response()->json(['message' => 'لا توجد كروت متاحة لهذه المكافأة حالياً'], 500);
        }

        // تسجيل السلفة
        $borrow = PointBorrow::create([
            'user_id' => $user->id,
            'amount' => $needed,
            'fee' => 0,
            'total_debt' => $needed,
            'repaid_amount' => 0,
            'status' => 'active',
        ]);

        // خصم قيمة المكافأة (يسمح بالسالب)
        $user->decrement('points_balance', $reward->points_cost);

        // منح الكرت للمستخدم
        $card->update([
            'status' => 'redeemed',
            'redeemed_by_user_id' => $user->id,
            'redeemed_at' => now(),
        ]);

        // تسجيل الحركة
        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'borrow',
            'amount' => -$reward->points_cost,
            'balance_after' => $user->points_balance,
            'reference_type' => 'absher',
            'reference_id' => $borrow->id,
            'note' => 'سلفة أبشر — فئة ' . $category->name,
        ]);

        return response()->json([
            'message' => 'تمت السلفة بنجاح',
            'borrow_id' => $borrow->id,
            'borrow_amount' => $needed,
            'reward_name' => $reward->name,
            'card_code' => $card->code,
            'points_balance' => $user->fresh()->points_balance,
        ]);
    }
}
