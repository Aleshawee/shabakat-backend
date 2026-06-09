<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Models\SmsMessage;
use App\Models\ActivityLog;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function history(Request $request)
    {
        $query = SmsMessage::query();

        if ($request->search) {
            $query->where('phone', 'like', "%{$request->search}%");
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->direction) {
            $query->where('direction', $request->direction);
        }

        if ($request->from_date) {
            $query->whereDate('sent_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('sent_at', '<=', $request->to_date);
        }

        $perPage = min((int) $request->per_page, 100) ?: 50;
        return response()->json($query->latest()->paginate($perPage));
    }

    public function stats(Request $request)
    {
        $query = SmsMessage::query();

        return response()->json([
            'total' => (clone $query)->count(),
            'sent' => (clone $query)->where('direction', 'outgoing')->where('status', 'sent')->count(),
            'failed' => (clone $query)->where('direction', 'outgoing')->where('status', 'failed')->count(),
            'received' => (clone $query)->where('direction', 'incoming')->count(),
        ]);
    }

    public function countTargetUsers(Request $request)
    {
        $query = User::query();

        if ($request->target === 'filtered') {
            if ($request->status) $query->where('status', $request->status);
            if ($request->points_min !== null) $query->where('points_balance', '>=', (int) $request->points_min);
            if ($request->points_max !== null) $query->where('points_balance', '<=', (int) $request->points_max);
            if ($request->from_date) $query->whereDate('created_at', '>=', $request->from_date);
            if ($request->to_date) $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->target === 'single' && $request->phone) {
            $query->where('phone', 'like', "%{$request->phone}%");
        }

        return response()->json(['count' => $query->count()]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|in:all,filtered,single',
            'phone' => 'required_if:target,single|nullable|string',
            'message' => 'required|string|max:500',
            'status' => 'nullable|in:active,suspended,banned',
            'points_min' => 'nullable|integer|min:0',
            'points_max' => 'nullable|integer|min:0',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $users = $this->getTargetUsers($request->user(), $validated);
        $sms = new SmsService();
        $sentCount = 0;
        $failedCount = 0;
        foreach ($users as $user) {
            $success = $sms->send($user->phone, $validated['message']);
            if ($success) $sentCount++;
            else $failedCount++;

            SmsMessage::create([
                'phone' => $user->phone,
                'message' => $validated['message'],
                'status' => $success ? 'sent' : 'failed',
                'sent_at' => now(),
            ]);
        }

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'sms_campaign',
            'target_type' => 'sms',
            'details' => "حملة SMS: {$sentCount} ناجحة, {$failedCount} فاشلة",
        ]);

        return response()->json([
            'message' => "تم الإرسال: {$sentCount} ناجحة, {$failedCount} فاشلة",
            'sent' => $sentCount,
            'failed' => $failedCount,
        ]);
    }

    public function fetchReceived(Request $request)
    {
        try {
            $admin = $request->user();
            $provider = Setting::where('key', 'sms_provider')->where('group', 'sms')->value('value');

            if ($provider === 'textbee') {
                $textbee = new \App\Services\TextbeeService();
                if (!$textbee->isEnabled()) {
                    return response()->json(['success' => false, 'fetched' => 0, 'messages' => [], 'error' => 'Textbee غير مفعّل']);
                }
                $messages = $textbee->fetchReceivedSms();
                return response()->json(['success' => true, 'fetched' => count($messages), 'messages' => $messages]);
            }

            return response()->json(['success' => false, 'fetched' => 0, 'messages' => [], 'error' => 'سحب الرسائل الواردة غير متاح لمزوّد SMS Gateway']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'fetched' => 0,
                'messages' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function testProvider(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:textbee,smsgateway',
            'phone' => 'required|string',
            'message' => 'required|string|max:200',
        ]);

        $result = match ($validated['provider']) {
            'textbee' => (new \App\Services\TextbeeService())->sendSmsWithDetails($validated['phone'], $validated['message']),
            'smsgateway' => (new \App\Services\SmsGatewayService())->sendSmsWithDetails($validated['phone'], $validated['message']),
        };

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] ? 'تم الإرسال بنجاح' : 'فشل: ' . $result['error'],
        ]);
    }

    private function getTargetUsers($admin, array $validated)
    {
        $query = User::query();

        if ($validated['target'] === 'all') {
            return $query->whereNotNull('phone')->get();
        }

        if ($validated['target'] === 'filtered') {
            if (!empty($validated['status'])) $query->where('status', $validated['status']);
            if (isset($validated['points_min'])) $query->where('points_balance', '>=', (int) $validated['points_min']);
            if (isset($validated['points_max'])) $query->where('points_balance', '<=', (int) $validated['points_max']);
            if (!empty($validated['from_date'])) $query->whereDate('created_at', '>=', $validated['from_date']);
            if (!empty($validated['to_date'])) $query->whereDate('created_at', '<=', $validated['to_date']);
            return $query->whereNotNull('phone')->get();
        }

        if ($validated['target'] === 'single') {
            $user = User::where('phone', 'like', "%{$validated['phone']}%")->first();
            return $user ? collect([$user]) : collect();
        }

        return collect();
    }
}
