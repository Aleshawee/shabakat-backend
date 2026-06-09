<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardCard;
use App\Models\PointTransaction;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        $rewards = Reward::where('is_active', true)->get();

        return response()->json(['rewards' => $rewards]);
    }

    public function redeem(Request $request, Reward $reward)
    {
        $user = $request->user();

        if (!$reward->is_active) {
            return response()->json(['message' => 'المكافأة غير متاحة'], 404);
        }

        if ($user->points_balance < $reward->points_cost) {
            return response()->json(['message' => 'رصيد النقاط غير كافٍ'], 403);
        }

        $user->decrement('points_balance', $reward->points_cost);

        $card = RewardCard::create([
            'reward_id' => $reward->id,
            'redeemed_by_user_id' => $user->id,
            'code' => 'RWRD-' . $reward->id . '-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'status' => 'available',
            'redeemed_at' => now(),
        ]);

        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'redeem',
            'amount' => -$reward->points_cost,
            'balance_after' => $user->points_balance,
            'reference_type' => 'reward',
            'reference_id' => $reward->id,
            'note' => 'استبدال مكافأة: ' . $reward->name,
        ]);

        return response()->json([
            'message' => 'تم استبدال المكافأة بنجاح',
            'card' => [
                'id' => $card->id,
                'code' => $card->code,
                'reward_name' => $reward->name,
            ],
            'points_balance' => $user->fresh()->points_balance,
        ]);
    }

    public function myCards(Request $request)
    {
        $cards = RewardCard::where('redeemed_by_user_id', $request->user()->id)
            ->with('reward')
            ->latest()
            ->get();

        return response()->json(['cards' => $cards]);
    }
}
