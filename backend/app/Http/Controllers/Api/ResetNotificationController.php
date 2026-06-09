<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\RestrictionController;
use App\Models\Setting;
use Illuminate\Http\Request;

class ResetNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        RestrictionController::runAutoResetIfNeeded();

        $settings = Setting::where('group', 'restrictions')
            ->get()
            ->pluck('value', 'key');

        $enabled = (bool) ($settings['reset_notification_enabled'] ?? false);
        if (!$enabled) {
            return response()->json(['enabled' => false, 'message' => null, 'days_left' => 0, 'reset_date' => null]);
        }

        $now = now()->startOfDay();
        $nextReset = now()->addMonthNoOverflow()->startOfMonth()->startOfDay();
        $daysLeft = (int) $now->diffInDays($nextReset, false);
        if ($daysLeft < 0) $daysLeft = 0;

        $rawMessage = $settings['reset_notification_message'] ?? '';
        if (empty(trim($rawMessage))) {
            $rawMessage = 'باقي {days} أيام لتصفير النقاط';
        }
        $message = str_replace('{days}', $daysLeft, $rawMessage);

        return response()->json([
            'enabled' => true,
            'message' => $message,
            'days_left' => $daysLeft,
            'reset_date' => $nextReset->format('Y-m-d'),
        ]);
    }
}
