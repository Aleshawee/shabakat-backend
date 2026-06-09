<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SportEvent;
use App\Models\SportPrediction;
use App\Models\PredictionSetting;
use App\Models\PointTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SportPredictionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $enabled = false;
        $maxActiveEvents = 5;
        $minTimeBeforeDeadline = 60;
        $allowEdit = false;
        $editFee = 0;

        $setting = PredictionSetting::first();
        if ($setting && $setting->is_enabled) {
            $enabled = true;
            $maxActiveEvents = $setting->max_active_events;
            $minTimeBeforeDeadline = $setting->min_time_before_deadline;
            $allowEdit = $setting->allow_prediction_edit;
            $editFee = $setting->edit_fee ?? 0;
        }

        $events = SportEvent::where('status', 'open')
            ->where('match_date', '>=', now())
            ->orderBy('match_date')
            ->get();

        // جلب توقعات المستخدم لهذه الأحداث
        $userPredictions = SportPrediction::where('user_id', $user->id)
            ->whereIn('event_id', $events->pluck('id'))
            ->get()
            ->keyBy('event_id');

        $result = $events->map(function ($event) use ($userPredictions) {
            $prediction = $userPredictions->get($event->id);
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'home_team' => $event->home_team,
                'away_team' => $event->away_team,
                'option_a_image' => $event->option_a_image,
                'option_b_image' => $event->option_b_image,
                'match_date' => $event->match_date,
                'prediction_deadline' => $event->prediction_deadline,
                'entry_fee' => $event->entry_fee,
                'reward_per_winner' => $event->reward_per_winner,
                'prediction_type' => $event->prediction_type,
                'allow_draw' => $event->allow_draw,
                'my_prediction' => $prediction ? [
                    'id' => $prediction->id,
                    'prediction' => $prediction->prediction,
                    'points_bet' => $prediction->points_bet,
                ] : null,
            ];
        });

        return response()->json([
            'enabled' => $enabled,
            'events' => $result,
            'max_active_events' => $maxActiveEvents,
            'min_time_before_deadline' => $minTimeBeforeDeadline,
            'allow_prediction_edit' => $allowEdit,
            'edit_fee' => $editFee,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $setting = PredictionSetting::first();
        if (!$setting || !$setting->is_enabled) {
            return response()->json(['message' => 'الميزة غير مفعلة'], 403);
        }

        $event = SportEvent::where('id', $id)->firstOrFail();

        $myPrediction = SportPrediction::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'home_team' => $event->home_team,
                'away_team' => $event->away_team,
                'option_a_image' => $event->option_a_image,
                'option_b_image' => $event->option_b_image,
                'match_date' => $event->match_date,
                'prediction_deadline' => $event->prediction_deadline,
                'entry_fee' => $event->entry_fee,
                'reward_per_winner' => $event->reward_per_winner,
                'prediction_type' => $event->prediction_type,
                'allow_draw' => $event->allow_draw,
                'status' => $event->status,
                'winner' => $event->winner,
            ],
            'my_prediction' => $myPrediction ? [
                'id' => $myPrediction->id,
                'prediction' => $myPrediction->prediction,
                'points_bet' => $myPrediction->points_bet,
                'is_winner' => $myPrediction->is_winner,
            ] : null,
        ]);
    }

    public function predict(Request $request, $id)
    {
        $user = $request->user();
        $setting = PredictionSetting::first();

        if (!$setting || !$setting->is_enabled) {
            return response()->json(['message' => 'الميزة غير مفعلة'], 403);
        }

        $event = SportEvent::where('id', $id)
            ->whereIn('status', ['open', 'closed'])
            ->firstOrFail();

        // التحقق من الموعد النهائي — الشرط الأساسي (باستخدام Asia/Riyadh)
        $riyadhNow = Carbon::now('Asia/Riyadh');
        $deadlineCarbon = $event->prediction_deadline ?? $event->match_date;
        if ($deadlineCarbon) {
            // القيمة المخزنة في DB هي بتوقيت الرياض (رقمياً)، لكن Carbon يعاملها كـ UTC
            // نعيد تفسيرها كتوقيت الرياض للمقارنة الصحيحة
            $deadline = Carbon::parse($deadlineCarbon->format('Y-m-d H:i:s'), 'Asia/Riyadh');
            if ($riyadhNow->greaterThanOrEqualTo($deadline)) {
                if ($event->status === 'open') {
                    $event->update(['status' => 'closed']);
                }
                return response()->json(['message' => 'انتهى وقت التوقع لهذه المباراة'], 422);
            }
        }

        if ($event->status !== 'open') {
            return response()->json(['message' => 'انتهى وقت التوقع لهذه المباراة'], 422);
        }

        $request->validate([
            'prediction' => 'required|string|max:255',
            'points_bet' => 'required|integer|min:0',
        ]);

        // التحقق من عدد التوقعات النشطة للمستخدم
        $activePredictionsCount = SportPrediction::where('user_id', $user->id)
            ->whereHas('event', function ($q) {
                $q->whereIn('status', ['open', 'closed']);
            })->count();

        if ($activePredictionsCount >= $setting->max_active_events) {
            return response()->json(['message' => 'لقد وصلت للحد الأقصى من التوقعات النشطة'], 422);
        }

        // التحقق من رصيد النقاط
        $fee = $request->points_bet;
        if ($fee > 0 && $user->points_balance < $fee) {
            return response()->json(['message' => 'رصيد نقاط غير كافٍ'], 422);
        }

        // السماح بالتعديل
        if ($setting->allow_prediction_edit) {
            $existing = SportPrediction::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();

            if ($existing) {
                $editFee = (int) ($setting->edit_fee ?? 0);

                if ($editFee > 0 && $user->points_balance < $editFee) {
                    return response()->json(['message' => 'رصيد نقاط غير كافٍ لرسوم التعديل'], 422);
                }

                if ($editFee > 0) {
                    $user->points_balance -= $editFee;
                    $user->save();
                    PointTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'prediction_edit_fee',
                        'amount' => -$editFee,
                        'balance_after' => $user->points_balance,
                        'reference_type' => 'sport_prediction',
                        'reference_id' => $existing->id,
                        'note' => "رسوم تعديل التوقع: {$editFee} نقطة",
                    ]);
                }

                // editFee = 0: فقط نحدث التوقع، لا نلمس النقاط
                $existing->update([
                    'prediction' => $request->prediction,
                ]);

                return response()->json(['message' => 'تم تعديل التوقع بنجاح', 'prediction' => $existing]);
            }
        } else {
            $exists = SportPrediction::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'سبق أن توقعت لهذه المباراة'], 422);
            }
        }

        // خصم رسوم المشاركة (غير مستردة)
        if ($fee > 0) {
            $user->points_balance -= $fee;
            $user->save();
        }

        $prediction = SportPrediction::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'prediction' => $request->prediction,
            'points_bet' => $fee,
        ]);

        if ($fee > 0) {
            PointTransaction::create([
                'user_id' => $user->id,
                'type' => 'prediction_bet',
                'amount' => -$fee,
                'balance_after' => $user->points_balance,
                'reference_type' => 'sport_prediction',
                'reference_id' => $prediction->id,
                'note' => 'رسوم مشاركة في التوقع (غير مستردة)',
            ]);
        }

        return response()->json(['message' => 'تم إرسال التوقع بنجاح', 'prediction' => $prediction]);
    }

    public function myPredictions(Request $request)
    {
        $user = $request->user();

        $predictions = SportPrediction::where('user_id', $user->id)
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($predictions);
    }
}
