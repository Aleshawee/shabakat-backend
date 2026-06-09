<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RewardCard;
use App\Models\NetworkCard;
use App\Models\PointTransaction;
use App\Models\Reward;
use App\Models\CardRedemption;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();

        $period = $request->input('period', '7days');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Determine date range
        if ($period === 'custom' && $startDate && $endDate) {
            $start = $startDate;
            $end = $endDate;
        } else {
            $days = match ($period) {
                '10days' => 10,
                '30days' => 30,
                default => 7,
            };
            $end = now()->format('Y-m-d');
            $start = now()->subDays($days)->format('Y-m-d');
        }

        $dateFilter = fn($q, $col = 'created_at') => $q->whereDate($col, '>=', $start)->whereDate($col, '<=', $end);

        // Stats cards
        $redeemedCards = NetworkCard::whereNotNull('used_by_user_id')
            ->whereDate('used_at', '>=', $start)->whereDate('used_at', '<=', $end)
            ->count();

        $pointsGiven = PointTransaction::where('type', 'admin_add')
            ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
            ->sum('amount');

        $activeUsers = User::where('status', 'active')->count();

        $totalUsers = User::count();

        $boxPlays = DB::table('lucky_box_plays')
            ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
            ->count();

        $wheelSpins = DB::table('lucky_wheel_plays')
            ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
            ->count();

        $predictionCount = PointTransaction::whereIn('type', ['prediction_bet', 'prediction_win'])
            ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
            ->count();

        // Daily activity chart — cards redeemed per day
        $dailyActivity = NetworkCard::whereNotNull('used_by_user_id')
            ->whereDate('used_at', '>=', $start)->whereDate('used_at', '<=', $end)
            ->selectRaw('DATE(used_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing dates with 0
        $dailyActivity = $this->fillDateGaps($dailyActivity, $start, $end);

        // Category distribution — cards by category (available)
        $categoryDist = RewardCard::whereNotNull('redeemed_by_user_id')
            ->whereDate('reward_cards.created_at', '>=', $start)->whereDate('reward_cards.created_at', '<=', $end)
            ->join('rewards', 'reward_cards.reward_id', '=', 'rewards.id')
            ->join('categories', 'rewards.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category, categories.points as points, count(reward_cards.id) as total_cards')
            ->groupBy('categories.id', 'categories.name', 'categories.points')
            ->orderBy('categories.id')
            ->get()
            ->map(fn($i) => [
                'category' => $i->category . ' (' . $i->points . ' نقطة)',
                'count' => (int) $i->total_cards,
            ]);

        // Points sources
        $pointsSources = PointTransaction::whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
            ->selectRaw('type, sum(amount) as total')
            ->groupBy('type')
            ->get()
            ->map(function ($i) {
                $label = match ($i->type) {
                    'admin_add' => 'إضافة إدارة',
                    'card_redeem' => 'كروت الشحن',
                    'lucky_box_win' => 'صناديق الحظ',
                    'lucky_wheel_win' => 'عجلة الحظ',
                    'prediction_win' => 'التوقعات',
                    'transfer_in' => 'تحويل وارد',
                    'spend' => 'إنفاق',
                    'lucky_box_spend' => 'رسوم صندوق الحظ',
                    'lucky_wheel_spend' => 'رسوم عجلة الحظ',
                    'transfer_out' => 'تحويل صادر',
                    default => $i->type,
                };
                return ['source' => $label, 'points' => (int) $i->total];
            });

        // Top 10 users by points earned
        $topUsers = User::join('point_transactions', 'users.id', '=', 'point_transactions.user_id')
            ->where('point_transactions.type', 'admin_add')
            ->whereDate('point_transactions.created_at', '>=', $start)
            ->whereDate('point_transactions.created_at', '<=', $end)
            ->selectRaw('users.id, users.name, users.phone, users.points_balance, sum(point_transactions.amount) as points_earned')
            ->groupBy('users.id', 'users.name', 'users.phone', 'users.points_balance')
            ->orderBy('points_earned', 'desc')
            ->take(10)
            ->get()
            ->map(function ($u) {
                $cardsCount = RewardCard::where('redeemed_by_user_id', $u->id)->count();
                $activities = PointTransaction::where('user_id', $u->id)->count();
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'phone' => $u->phone,
                    'cards_count' => $cardsCount,
                    'points_earned' => (int) $u->points_earned,
                    'points_balance' => (int) $u->points_balance,
                    'activities' => $activities,
                ];
            });

        return response()->json([
            'period' => ['start' => $start, 'end' => $end],
            'stats' => [
                'cards_redeemed' => $redeemedCards,
                'points_given' => (int) $pointsGiven,
                'active_users' => $activeUsers,
                'total_users' => $totalUsers,
                'box_opens' => $boxPlays,
                'wheel_spins' => $wheelSpins,
                'predictions' => $predictionCount,
            ],
            'daily_activity' => $dailyActivity,
            'category_distribution' => $categoryDist,
            'points_sources' => $pointsSources,
            'top_users' => $topUsers,
        ]);
    }

    private function fillDateGaps($data, $start, $end)
    {
        $filled = [];
        $indexed = [];
        foreach ($data as $d) {
            $indexed[$d->date] = $d->count;
        }
        $current = \Carbon\Carbon::parse($start);
        $endDate = \Carbon\Carbon::parse($end);
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $filled[] = [
                'date' => $dateStr,
                'count' => (int) ($indexed[$dateStr] ?? 0),
            ];
            $current->addDay();
        }
        return $filled;
    }
}
