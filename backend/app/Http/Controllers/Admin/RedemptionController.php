<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardCard;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Http\Request;

class RedemptionController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();

        $query = RewardCard::whereNotNull('redeemed_by_user_id')
            ->with(['reward', 'redeemedBy']);

        $phone = $request->input('search');
        $rewardId = $request->input('reward_id');
        $userIds = collect();

        // Search by phone
        if ($phone) {
            $phone = preg_replace('/^\+?967/', '', $phone);
            $userIds = User::where(function ($q) use ($phone) {
                    $q->where('phone', $phone)->orWhere('phone', '+967' . $phone);
                })->pluck('id');
            $query->whereIn('redeemed_by_user_id', $userIds);
        }

        // Filter by reward
        if ($rewardId) {
            $query->where('reward_id', $rewardId);
        }

        // Total points spent (separate query to avoid join interference with eager loading)
        $totalQuery = RewardCard::whereNotNull('redeemed_by_user_id')
            ->join('rewards', 'reward_cards.reward_id', '=', 'rewards.id');
        if ($phone) {
            $totalQuery->whereIn('reward_cards.redeemed_by_user_id', $userIds);
        }
        if ($rewardId) {
            $totalQuery->where('reward_cards.reward_id', $rewardId);
        }
        $totalPointsSpent = (int) (clone $totalQuery)->sum('rewards.points_cost');
        $totalCardValue = (float) (clone $totalQuery)->sum('rewards.card_value');

        $perPage = min((int) $request->input('per_page', 20), 100);
        $redemptions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Format the response
        $data = $redemptions->map(function ($card) {
            return [
                'id' => $card->id,
                'user_phone' => $card->redeemedBy?->phone,
                'user_name' => $card->redeemedBy?->name,
                'reward_name' => $card->reward?->name,
                'reward_id' => $card->reward_id,
                'points_spent' => $card->reward?->points_cost,
                'card_value' => $card->reward?->card_value,
                'card_code' => $card->code,
                'created_at' => $card->created_at,
            ];
        });

        // Get all rewards for filter dropdown
        $rewards = Reward::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $data,
            'total' => $redemptions->total(),
            'total_points_spent' => (int) $totalPointsSpent,
            'total_card_value' => $totalCardValue,
            'per_page' => $redemptions->perPage(),
            'current_page' => $redemptions->currentPage(),
            'last_page' => $redemptions->lastPage(),
            'rewards' => $rewards,
        ]);
    }
}
