<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SportEvent;
use App\Models\SportPrediction;
use App\Models\PredictionSetting;
use App\Models\PointTransaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SportEventController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        $query = SportEvent::withCount('predictions');

        if ($request->search) $query->where('title', 'like', "%{$request->search}%");
        if ($request->status) $query->where('status', $request->status);
        if ($request->from_date) $query->whereDate('match_date', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('match_date', '<=', $request->to_date);

        $perPage = min((int) $request->per_page, 100) ?: 50;
        return response()->json($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $admin = $request->user();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:match,election,custom',
            'prediction_type' => 'required|in:winner,exact_score',
            'allow_draw' => 'boolean',
            'home_team' => 'nullable|string|max:255',
            'away_team' => 'nullable|string|max:255',
            'option_a_image' => 'nullable|string',
            'option_b_image' => 'nullable|string',
            'match_date' => 'required|date',
            'prediction_deadline' => 'nullable|date',
            'entry_fee' => 'required|integer|min:0',
            'reward_per_winner' => 'required|integer|min:0',
            'status' => 'required|in:open,closed,settled,completed,cancelled',
        ]);

        $event = SportEvent::create($data);

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'create',
            'target_type' => 'sport_event',
            'target_id' => $event->id,
            'details' => "إضافة حدث رياضي: {$event->title}",
        ]);

        return response()->json($event, 201);
    }

    public function show($id)
    {
        $event = SportEvent::with(['predictions' => function ($q) { $q->with('user')->latest(); }])->findOrFail($id);
        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $event = SportEvent::findOrFail($id);

        $data = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'in:match,election,custom',
            'prediction_type' => 'in:winner,exact_score',
            'allow_draw' => 'boolean',
            'home_team' => 'nullable|string|max:255',
            'away_team' => 'nullable|string|max:255',
            'option_a_image' => 'nullable|string',
            'option_b_image' => 'nullable|string',
            'match_date' => 'date',
            'prediction_deadline' => 'nullable|date',
            'entry_fee' => 'integer|min:0',
            'reward_per_winner' => 'integer|min:0',
            'status' => 'in:open,closed,settled,completed,cancelled',
            'winner' => 'nullable|in:home,away,draw',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
        ]);

        $oldStatus = $event->status;
        $oldWinner = $event->winner;
        $oldHomeScore = $event->home_score;
        $oldAwayScore = $event->away_score;

        $event->update($data);

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'update',
            'target_type' => 'sport_event',
            'target_id' => $event->id,
            'details' => "تعديل حدث رياضي: {$event->title}",
        ]);

        // توزيع الجوائز تلقائياً عند إكمال الحدث أول مرة
        if (($data['status'] ?? null) === 'completed' && ($data['winner'] ?? null) && $oldStatus !== 'completed') {
            $this->processPredictions($event, false);
        }

        // إعادة التوزيع عند تعديل النتيجة على حدث مكتمل
        if ($oldStatus === 'completed') {
            $resultChanged = (isset($data['winner']) && $data['winner'] !== $oldWinner)
                || (isset($data['home_score']) && (int)$data['home_score'] !== (int)$oldHomeScore)
                || (isset($data['away_score']) && (int)$data['away_score'] !== (int)$oldAwayScore);
            if ($resultChanged) {
                $this->processPredictions($event, true);
            }
        }

        return response()->json($event);
    }

    public function distribute(Request $request, $id)
    {
        $event = SportEvent::findOrFail($id);

        if ($event->status !== 'completed' || !$event->winner) {
            return response()->json(['message' => 'الحدث لم يكتمل بعد'], 422);
        }

        if ($event->rewards_distributed) {
            return response()->json(['message' => 'تم توزيع الجوائز مسبقاً'], 422);
        }

        $this->processPredictions($event, false, true);
        return response()->json(['message' => 'تم توزيع الجوائز']);
    }

    public function destroy(Request $request, $id)
    {
        $event = SportEvent::findOrFail($id);
        $event->delete();

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'delete',
            'target_type' => 'sport_event',
            'target_id' => $event->id,
            'details' => "حذف حدث رياضي: {$event->title}",
        ]);

        return response()->json(['message' => 'تم الحذف']);
    }

    private function processPredictions($event, $reset = false, $forceDistribute = false)
    {
        $predictions = SportPrediction::where('event_id', $event->id)->get();
        if ($predictions->isEmpty()) return;

        // إذا كان التوزيع تم مسبقاً وليس إعادة (من تعديل النتيجة)، نمنع التكرار
        if ($event->rewards_distributed && !$reset) return;

        $setting = PredictionSetting::first();
        $autoDistribute = $setting && $setting->auto_distribute_rewards;
        $shouldDistribute = $forceDistribute || $autoDistribute;

        foreach ($predictions as $prediction) {
            // عند إعادة التوزيع، نمسح old is_winner أولاً
            if ($reset && $prediction->is_winner !== null) {
                $prediction->is_winner = null;
                $prediction->save();
            }

            $isWinner = $this->checkWinner($prediction, $event);

            $prediction->is_winner = $isWinner;
            $prediction->save();

            if (!$shouldDistribute) continue;

            $user = $prediction->user;
            if (!$user) continue;

            if ($isWinner) {
                // فقط المكافأة — رسوم المشاركة غير مستردة
                $user->points_balance += $event->reward_per_winner;
                $user->save();

                PointTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'prediction_win',
                    'amount' => $event->reward_per_winner,
                    'balance_after' => $user->points_balance,
                    'reference_type' => 'sport_prediction',
                    'reference_id' => $prediction->id,
                    'note' => "جائزة فوز في توقع: {$event->title}",
                ]);
            }
            // الخاسر لا يسترد شيئاً — رسوم المشاركة غير مستردة
        }

        // فقط نضع علامة "تم الصرف" إذا تم التوزيع الفعلي
        if ($shouldDistribute && !$event->rewards_distributed) {
            $event->update(['rewards_distributed' => true]);
        }
    }

    private function checkWinner($prediction, $event)
    {
        $userPred = $prediction->prediction;

        if ($event->prediction_type === 'exact_score') {
            if ($event->home_score === null || $event->away_score === null) return false;
            $actualScore = $event->home_score . '-' . $event->away_score;
            return $userPred === $actualScore;
        }

        // winner type
        $winnerMap = [
            'home' => $event->home_team,
            'away' => $event->away_team,
            'draw' => 'draw',
        ];

        $expectedWinner = $winnerMap[$event->winner] ?? null;
        if (!$expectedWinner) return false;

        return mb_strtolower(trim($userPred)) === mb_strtolower(trim($expectedWinner));
    }

    public function stats(Request $request)
    {
        $totalPredictions = SportPrediction::count();
        $totalWinners = SportPrediction::where('is_winner', true)->count();
        $totalLosers = SportPrediction::where('is_winner', false)->count();
        $totalPending = SportPrediction::whereNull('is_winner')->count();

        $totalEvents = SportEvent::count();
        $completedEvents = SportEvent::where('status', 'completed')->count();

        $totalFees = PointTransaction::whereIn('type', ['prediction_bet', 'prediction_edit_fee'])
            ->where('amount', '<', 0)
            ->sum(DB::raw('ABS(amount)'));

        $totalRewards = PointTransaction::where('type', 'prediction_win')
            ->sum('amount');

        $totalParticipants = SportPrediction::distinct('user_id')
            ->count('user_id');

        return response()->json([
            'total_predictions' => $totalPredictions,
            'total_winners' => $totalWinners,
            'total_losers' => $totalLosers,
            'total_pending' => $totalPending,
            'total_events' => $totalEvents,
            'completed_events' => $completedEvents,
            'total_fees' => (int) $totalFees,
            'total_rewards' => (int) $totalRewards,
            'total_participants' => $totalParticipants,
            'win_rate' => $totalPredictions > 0 ? round(($totalWinners / $totalPredictions) * 100, 1) : 0,
        ]);
    }

}
