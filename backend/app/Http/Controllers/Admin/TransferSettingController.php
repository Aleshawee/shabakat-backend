<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransferSetting;
use App\Models\PointTransfer;
use App\Models\User;
use Illuminate\Http\Request;

class TransferSettingController extends Controller
{
    public function show(Request $request)
    {
        $admin = $request->user();

        $setting = TransferSetting::firstOrCreate(
            [],
            ['is_enabled' => false, 'min_transfer_amount' => 10, 'max_transfer_amount' => 1000, 'transfer_fee_percent' => 0, 'min_balance_required' => 0, 'require_phone_verification' => false]
        );

        $stats = [
            'total_transfers' => PointTransfer::count(),
            'total_amount' => (int) PointTransfer::sum('amount'),
            'total_fees' => (int) PointTransfer::sum('fee'),
            'total_senders' => PointTransfer::distinct('sender_id')->count('sender_id'),
            'total_receivers' => PointTransfer::distinct('receiver_id')->count('receiver_id'),
            'recent_transfers' => PointTransfer::with(['sender:id,phone,name', 'receiver:id,phone,name'])
                ->latest()
                ->take(10)
                ->get(),
        ];

        return response()->json([
            'setting' => $setting,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request)
    {
        $admin = $request->user();
        $data = $request->validate([
            'is_enabled' => 'boolean',
            'min_transfer_amount' => 'integer|min:1',
            'max_transfer_amount' => 'integer|min:1',
            'transfer_fee_percent' => 'numeric|min:0|max:100',
            'min_balance_required' => 'integer|min:0',
            'require_phone_verification' => 'boolean',
        ]);

        $setting = TransferSetting::firstOrCreate([]);
        $setting->update($data);
        return response()->json(['setting' => $setting]);
    }

    public function lookupUser(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $user = User::where('phone', $request->phone)
            ->select('id', 'name', 'phone', 'points_balance')
            ->first();

        if (!$user) {
            return response()->json(['found' => false, 'message' => 'رقم الجوال غير موجود في النظام'], 404);
        }

        return response()->json(['found' => true, 'user' => $user]);
    }
}
