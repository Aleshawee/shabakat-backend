<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NetworkCard;
use App\Models\PointTransaction;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        // التحقق من السقف اليومي
        $limit = (int) Setting::where('key', 'daily_card_redeem_limit')
            ->where('group', 'restrictions')
            ->value('value');

        if ($limit > 0) {
            $todayCount = PointTransaction::where('user_id', $user->id)
                ->where('type', 'card_redeem')
                ->whereDate('created_at', today())
                ->count();

            if ($todayCount >= $limit) {
                $action = Setting::where('key', 'daily_card_exceed_action')
                    ->where('group', 'restrictions')
                    ->value('value');

                if ($action === 'suspend') {
                    $user->update(['status' => 'suspended']);
                    return response()->json(['message' => 'تم حظر حسابك بسبب تجاوز الحد اليومي لاستبدال الكروت'], 403);
                }

                return response()->json(['message' => 'تجاوزت الحد اليومي المسموح لاستبدال الكروت'], 429);
            }
        }

        $card = NetworkCard::where('code', $validated['code'])
            ->where('status', 'active')
            ->first();

        if (!$card) {
            return response()->json(['message' => 'الكرت غير موجود أو مستخدم مسبقاً'], 404);
        }

        if (!$card->category_id) {
            return response()->json(['message' => 'هذا الكرت غير مرتبط بفئة'], 400);
        }

        $category = $card->category;
        if (!$category || !$category->points) {
            return response()->json(['message' => 'لا توجد نقاط محددة لهذا الكرت'], 400);
        }

        $points = $category->points;

        $user->addPointsWithRepayment($points, 'network_card', $card->id, 'استبدال كرت: ' . $card->code);

        $card->update([
            'status' => 'used',
            'used_at' => now(),
            'used_by_user_id' => $user->id,
        ]);

        $finalBalance = $user->fresh()->points_balance;

        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'card_redeem',
            'amount' => $points,
            'balance_after' => $finalBalance,
            'reference_type' => 'network_card',
            'reference_id' => $card->id,
            'note' => 'استبدال كرت: ' . $card->code,
        ]);

        return response()->json([
            'message' => 'تم استبدال الكرت بنجاح',
            'points_earned' => $points,
            'card_name' => $category->name,
            'points_balance' => $finalBalance,
        ]);
    }
}
