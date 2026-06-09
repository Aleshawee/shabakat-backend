<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransferSetting;
use App\Models\PointTransfer;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function settings(Request $request)
    {
        $user = $request->user();
        $setting = TransferSetting::first();

        if (!$setting || !$setting->is_enabled) {
            return response()->json(['enabled' => false, 'message' => 'الميزة غير مفعلة']);
        }

        return response()->json([
            'enabled' => true,
            'min_amount' => $setting->min_transfer_amount,
            'max_amount' => $setting->max_transfer_amount,
            'fee_percent' => $setting->transfer_fee_percent,
            'min_balance_required' => $setting->min_balance_required,
            'require_phone_verification' => $setting->require_phone_verification,
        ]);
    }

    public function lookupUser(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $user = $request->user();
        $setting = TransferSetting::first();

        if (!$setting || !$setting->is_enabled) {
            return response()->json(['message' => 'الميزة غير مفعلة'], 403);
        }

        if (!$setting->require_phone_verification) {
            return response()->json(['message' => 'التحقق من رقم الجوال غير مفعل'], 403);
        }

        $normalized = preg_replace('/^\+?967/', '', $request->phone);
        $recipient = User::where('phone', $normalized)
            ->select('id', 'name', 'phone')
            ->first();

        if (!$recipient) {
            $recipient = User::where('phone', '+967' . $normalized)
                ->select('id', 'name', 'phone')
                ->first();
        }

        if (!$recipient) {
            return response()->json(['found' => false, 'message' => 'رقم الجوال غير موجود في النظام'], 404);
        }

        if ($recipient->id === $user->id) {
            return response()->json(['found' => false, 'message' => 'لا يمكن التحويل لنفسك'], 422);
        }

        return response()->json(['found' => true, 'user' => [
            'id' => $recipient->id,
            'name' => $recipient->name,
            'phone' => $recipient->phone,
        ]]);
    }

    public function send(Request $request)
    {
        $user = $request->user();
        $setting = TransferSetting::first();

        if (!$setting || !$setting->is_enabled) {
            return response()->json(['message' => 'الميزة غير مفعلة'], 403);
        }

        $data = $request->validate([
            'receiver_phone' => 'required|string',
            'amount' => 'required|integer|min:' . ($setting->min_transfer_amount ?? 1) . '|max:' . ($setting->max_transfer_amount ?? 999999),
        ]);

        $amount = (int) $data['amount'];

        // العثور على المستلم — يدعم الإرسال بمفتاح البلد أو بدونه
        $normalized = preg_replace('/^\+?967/', '', $data['receiver_phone']);
        $recipient = User::where('phone', $normalized)
            ->first();

        if (!$recipient) {
            $recipient = User::where('phone', '+967' . $normalized)
                ->first();
        }

        if (!$recipient) {
            return response()->json(['message' => 'المستلم غير موجود في النظام'], 404);
        }

        if ($recipient->id === $user->id) {
            return response()->json(['message' => 'لا يمكن التحويل لنفسك'], 422);
        }

        // التحقق من رقم الجوال إذا مفعل
        if ($setting->require_phone_verification && !$recipient) {
            return response()->json(['message' => 'رقم الجوال غير موجود في النظام'], 404);
        }

        // حساب الرسوم
        $fee = (int) round($amount * ($setting->transfer_fee_percent / 100));
        $netAmount = $amount - $fee;
        $gross = $amount;

        // التحقق من الرصيد
        $requiredBalance = max($setting->min_balance_required ?? 0, $gross);
        if ($user->points_balance < $requiredBalance) {
            return response()->json(['message' => 'رصيدك غير كافٍ للتحويل (الرصيد: ' . $user->points_balance . '، المطلوب: ' . $requiredBalance . ')'], 403);
        }

        // خصم من المحوِّل
        $user->decrement('points_balance', $gross);

        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'transfer_out',
            'amount' => -$gross,
            'balance_after' => $user->points_balance,
            'reference_type' => 'transfer',
            'note' => 'تحويل نقاط إلى ' . $recipient->phone,
        ]);

        // إضافة للمستلم (صافي بعد الرسوم) مع تسديد السلفة
        $recipient->addPointsWithRepayment($netAmount, 'transfer', null, 'استلام نقاط من ' . $user->phone);

        PointTransaction::create([
            'user_id' => $recipient->id,
            'type' => 'transfer_in',
            'amount' => $netAmount,
            'balance_after' => $recipient->fresh()->points_balance,
            'reference_type' => 'transfer',
            'note' => 'استلام نقاط من ' . $user->phone,
        ]);

        // تسجيل التحويل
        $transfer = PointTransfer::create([
            'sender_id' => $user->id,
            'receiver_id' => $recipient->id,
            'amount' => $netAmount,
            'fee' => $fee,
            'gross_amount' => $gross,
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => 'تم التحويل بنجاح',
            'transfer_id' => $transfer->id,
            'amount' => $netAmount,
            'fee' => $fee,
            'gross_amount' => $gross,
            'recipient' => $recipient->name,
            'points_balance' => $user->fresh()->points_balance,
        ]);
    }
}
