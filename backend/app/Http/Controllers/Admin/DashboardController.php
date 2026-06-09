<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RewardCard;
use App\Models\Reward;
use App\Models\CardRedemption;
use App\Models\PointTransaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();

        // ---- Stats cards ----
        $totalUsers = User::count();
        $totalPointsGiven = PointTransaction::where('type', 'admin_add')->sum('amount');
        $totalPointsUsed = RewardCard::whereNotNull('redeemed_by_user_id')
            ->join('rewards', 'reward_cards.reward_id', '=', 'rewards.id')
            ->sum('rewards.points_cost');

        $rewardCardsAvailable = RewardCard::where('status', 'available')->count();

        // ---- Reward card inventory by card_value ----
        $inventory = RewardCard::where('reward_cards.status', 'available')
            ->join('rewards', 'reward_cards.reward_id', '=', 'rewards.id')
            ->selectRaw('rewards.card_value, count(*) as count')
            ->groupBy('rewards.card_value')
            ->orderBy('rewards.card_value')
            ->get()
            ->keyBy('card_value');

        $rewardValues = Reward::where('is_active', true)
            ->whereNotNull('card_value')
            ->orderBy('card_value')
            ->pluck('card_value')
            ->unique()
            ->values();

        $inventoryData = [];
        foreach ($rewardValues as $val) {
            $inventoryData[] = [
                'card_value' => (float) $val,
                'remaining' => (int) ($inventory[(string) $val]->count ?? $inventory[$val]->count ?? 0),
            ];
        }

        // ---- New users registration chart ----
        $days = (int) $request->input('days', 14);
        $registrationChart = User::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ---- Top users by points ----
        $topByPoints = User::where('status', 'active')
            ->orderBy('points_balance', 'desc')
            ->take(5)
            ->get(['id', 'name', 'phone', 'points_balance']);

        // ---- Top users by redemptions ----
        $topByRedemptions = User::join('reward_cards', 'users.id', '=', 'reward_cards.redeemed_by_user_id')
            ->whereNotNull('reward_cards.redeemed_by_user_id')
            ->selectRaw('users.id, users.name, users.phone, users.points_balance, count(reward_cards.id) as redemption_count')
            ->groupBy('users.id', 'users.name', 'users.phone', 'users.points_balance')
            ->orderBy('redemption_count', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_users' => $totalUsers,
                'reward_cards_available' => $rewardCardsAvailable,
                'total_points_given' => (int) $totalPointsGiven,
                'total_points_used' => (int) $totalPointsUsed,
            ],
            'card_inventory' => $inventoryData,
            'registration_chart' => $registrationChart,
            'top_users' => [
                'by_points' => $topByPoints,
                'by_redemptions' => $topByRedemptions,
            ],
        ]);
    }
}
