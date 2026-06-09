<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LuckyWheel;
use App\Models\LuckyWheelPlay;
use App\Models\PointTransaction;
use App\Models\Reward;
use App\Models\RewardCard;
use Illuminate\Http\Request;

class LuckyWheelController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $wheels = LuckyWheel::with('prizes')
            ->where('is_active', true)
            ->get()
            ->map(function ($wheel) use ($user) {
                $todayPlays = LuckyWheelPlay::where('user_id', $user->id)
                    ->where('lucky_wheel_id', $wheel->id)
                    ->whereDate('created_at', today())
                    ->count();

                $wheel->today_spins = $todayPlays;
                $wheel->remaining_spins = $wheel->daily_limit > 0
                    ? max(0, $wheel->daily_limit - $todayPlays)
                    : -1;

                $wheel->next_spin_cost = $this->calcSpinCost($wheel, $todayPlays);
                $wheel->can_spin_free = $this->canSpinFree($wheel, $todayPlays);

                $lastSpin = LuckyWheelPlay::where('user_id', $user->id)
                    ->where('lucky_wheel_id', $wheel->id)
                    ->latest('created_at')
                    ->value('created_at');
                $wheel->last_spin_at = $lastSpin;

                return $wheel;
            });

        return response()->json(['wheels' => $wheels]);
    }

    public function spin(Request $request, LuckyWheel $wheel)
    {
        $user = $request->user();

        if (!$wheel->is_active) {
            return response()->json(['message' => 'العجلة غير متاحة'], 404);
        }

        $todayPlays = LuckyWheelPlay::where('user_id', $user->id)
            ->where('lucky_wheel_id', $wheel->id)
            ->whereDate('created_at', today())
            ->count();

        if ($wheel->daily_limit > 0 && $todayPlays >= $wheel->daily_limit) {
            return response()->json(['message' => 'لقد استنفذت عدد مرات اللعب اليومية'], 403);
        }

        $spinCost = 0;
        $canSpinFree = $this->canSpinFree($wheel, $todayPlays);

        if ($canSpinFree) {
            $spinCost = 0;
        } elseif ($wheel->spin_mode === 'daily_free_only') {
            return response()->json(['message' => 'لقد استخدمت اللفة المجانية لهذا اليوم'], 403);
        } else {
            $spinCost = $wheel->point_cost;
        }

        if ($spinCost > 0 && $user->points_balance < $spinCost) {
            return response()->json(['message' => 'رصيد النقاط غير كافٍ'], 403);
        }

        $prizes = $wheel->prizes;
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

        if ($spinCost > 0) {
            $user->decrement('points_balance', $spinCost);
        }

        // معالجة الجائزة
        $prizePoints = 0;
        $prizeNote = 'لم تفز بشيء';
        $prizeCard = null;
        if ($selected->type === 'point') {
            $prizePoints = (int) $selected->value;
            $user->addPointsWithRepayment($prizePoints, 'lucky_wheel', $wheel->id, "فوز بعجلة الحظ: {$wheel->name}");
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

        $todayPlaysAfter = LuckyWheelPlay::where('user_id', $user->id)
            ->where('lucky_wheel_id', $wheel->id)
            ->whereDate('created_at', today())
            ->count();

        $finalBalance = $user->fresh()->points_balance;

        LuckyWheelPlay::create([
            'user_id' => $user->id,
            'lucky_wheel_id' => $wheel->id,
            'prize_id' => $selected->id,
            'points_spent' => $spinCost,
            'result' => $selected->type,
        ]);

        if ($spinCost > 0) {
            PointTransaction::create([
                'user_id' => $user->id,
                'type' => 'lucky_wheel_spend',
                'amount' => -$spinCost,
                'balance_after' => $finalBalance,
                'reference_type' => 'lucky_wheel',
                'reference_id' => $wheel->id,
                'note' => "لف عجلة الحظ: {$wheel->name}",
            ]);
        }

        if ($prizePoints > 0) {
            PointTransaction::create([
                'user_id' => $user->id,
                'type' => 'lucky_wheel_win',
                'amount' => $prizePoints,
                'balance_after' => $finalBalance,
                'reference_type' => 'lucky_wheel',
                'reference_id' => $wheel->id,
                'note' => $prizeNote,
            ]);
        }
        if ($prizeCard) {
            PointTransaction::create([
                'user_id' => $user->id,
                'type' => 'lucky_wheel_win',
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
            'wheel' => $wheel->only(['id', 'name', 'spin_mode', 'point_cost', 'daily_limit']),
            'was_free' => $canSpinFree,
            'spin_cost' => $spinCost,
            'remaining_spins' => $wheel->daily_limit > 0
                ? max(0, $wheel->daily_limit - $todayPlaysAfter)
                : -1,
            'next_spin_cost' => $this->calcSpinCost($wheel, $todayPlaysAfter),
            'can_spin_free_next' => $this->canSpinFree($wheel, $todayPlaysAfter),
            'points_balance' => $finalBalance,
        ]);
    }

    private function canSpinFree(LuckyWheel $wheel, int $todayPlays): bool
    {
        return match ($wheel->spin_mode) {
            'daily_then_points' => $todayPlays === 0,
            'daily_free_only' => $todayPlays === 0,
            default => false,
        };
    }

    private function calcSpinCost(LuckyWheel $wheel, int $todayPlays): int
    {
        return match ($wheel->spin_mode) {
            'daily_then_points' => $todayPlays === 0 ? 0 : $wheel->point_cost,
            'daily_free_only' => 0,
            default => $wheel->point_cost,
        };
    }
}
