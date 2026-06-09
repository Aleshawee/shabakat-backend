<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LuckyBox;
use App\Models\LuckyBoxPlay;
use App\Models\PointTransaction;
use App\Models\Reward;
use App\Models\RewardCard;
use Illuminate\Http\Request;

class LuckyBoxController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $boxes = LuckyBox::with('prizes')
            ->where('is_active', true)
            ->get()
            ->map(function ($box) use ($user) {
                $todayPlays = LuckyBoxPlay::where('user_id', $user->id)
                    ->where('lucky_box_id', $box->id)
                    ->whereDate('created_at', today())
                    ->count();

                $box->today_plays = $todayPlays;
                $box->remaining_plays = $box->daily_limit > 0
                    ? max(0, $box->daily_limit - $todayPlays)
                    : -1;
                return $box;
            });

        return response()->json(['boxes' => $boxes]);
    }

    public function play(Request $request, LuckyBox $box)
    {
        $user = $request->user();

        if (!$box->is_active) {
            return response()->json(['message' => 'الصندوق غير متاح'], 404);
        }

        if ($user->points_balance < $box->cost) {
            return response()->json(['message' => 'رصيد النقاط غير كافٍ'], 403);
        }

        if ($box->daily_limit > 0) {
            $todayPlays = LuckyBoxPlay::where('user_id', $user->id)
                ->where('lucky_box_id', $box->id)
                ->whereDate('created_at', today())
                ->count();

            if ($todayPlays >= $box->daily_limit) {
                return response()->json(['message' => 'لقد استنفذت عدد مرات اللعب اليومية'], 403);
            }
        }

        $prizes = $box->prizes;
        $totalWeight = $prizes->sum('weight');

        if ($totalWeight <= 0) {
            return response()->json(['message' => 'لا توجد جوائز متاحة'], 500);
        }

        $rand = rand(1, $totalWeight);
        $selected = null;
        foreach ($prizes as $prize) {
            $rand -= $prize->weight;
            if ($rand <= 0) { $selected = $prize; break; }
        }

        if (!$selected) $selected = $prizes->last();

        // خصم تكلفة اللعب
        $user->decrement('points_balance', $box->cost);

        // معالجة الجائزة
        $prizePoints = 0;
        $prizeNote = 'لم تفز بشيء';
        $prizeCard = null;
        if ($selected->type === 'point') {
            $prizePoints = (int) $selected->value;
            $user->addPointsWithRepayment($prizePoints, 'lucky_box', $box->id, "فوز بصندوق الحظ: {$box->name}");
            $prizeNote = "فزت بـ {$prizePoints} نقطة";
        } elseif ($selected->type === 'card') {
            $categoryId = (int) $selected->value;
            $reward = Reward::where('category_id', $categoryId)
                ->first();
            if ($reward) {
                $card = RewardCard::where('reward_id', $reward->id)
                    ->where('status', 'available')
                    ->first();
                if ($card) {
                    $card->update([
                        'status' => 'redeemed',
                        'redeemed_by_user_id' => $user->id,
                        'redeemed_at' => now(),
                    ]);
                    $prizeCard = $card;
                    $prizeNote = "فزت بكارت: {$card->code}";
                } else {
                    $prizeNote = "{$selected->name} (نفذت الكروت)";
                }
            } else {
                $prizeNote = "{$selected->name} (لا توجد مكافأة)";
            }
        }

        LuckyBoxPlay::create([
            'user_id' => $user->id,
            'lucky_box_id' => $box->id,
            'prize_id' => $selected->id,
            'points_spent' => $box->cost,
            'result' => $selected->type,
        ]);

        $todayPlays = LuckyBoxPlay::where('user_id', $user->id)
            ->where('lucky_box_id', $box->id)
            ->whereDate('created_at', today())
            ->count();

        $finalBalance = $user->fresh()->points_balance;

        // تسجيل حركة الخصم (قبل إضافة الربح)
        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'lucky_box_spend',
            'amount' => -$box->cost,
            'balance_after' => $finalBalance,
            'reference_type' => 'lucky_box',
            'reference_id' => $box->id,
            'note' => "لعب صندوق الحظ: {$box->name}",
        ]);

        // تسجيل حركة الربح
        if ($prizePoints > 0) {
            PointTransaction::create([
                'user_id' => $user->id,
                'type' => 'lucky_box_win',
                'amount' => $prizePoints,
                'balance_after' => $finalBalance,
                'reference_type' => 'lucky_box',
                'reference_id' => $box->id,
                'note' => $prizeNote,
            ]);
        }
        if ($prizeCard) {
            PointTransaction::create([
                'user_id' => $user->id,
                'type' => 'lucky_box_win',
                'amount' => 0,
                'balance_after' => $finalBalance,
                'reference_type' => 'reward_card',
                'reference_id' => $prizeCard->id,
                'note' => $prizeNote,
            ]);
        }

        return response()->json([
            'prize' => $selected,
            'card_code' => $prizeCard ? $prizeCard->code : null,
            'box' => $box->only(['id', 'name', 'cost', 'daily_limit']),
            'remaining_plays' => $box->daily_limit > 0
                ? max(0, $box->daily_limit - $todayPlays)
                : -1,
            'points_balance' => $finalBalance,
        ]);
    }
}
