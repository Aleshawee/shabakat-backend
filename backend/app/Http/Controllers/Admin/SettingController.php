<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function sms(Request $request)
    {
        $admin = $request->user();
        if (!$admin->isOwner()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $settings = Setting::where('group', 'sms')
            ->get()
            ->pluck('value', 'key');

        return response()->json($settings);
    }

    public function updateSms(Request $request)
    {
        $admin = $request->user();
        if (!$admin->isOwner()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $data = $request->validate([
            'sms_provider' => 'nullable|string|max:255',
            'sms_api_key' => 'nullable|string|max:255',
            'sms_api_secret' => 'nullable|string|max:255',
            'sms_sender_name' => 'nullable|string|max:255',
            'sms_enabled' => 'nullable|in:true,false,1,0',
            'textbee_api_key' => 'nullable|string|max:255',
            'textbee_device_id' => 'nullable|string|max:255',
            'smsgateway_url' => 'nullable|string|max:255',
            'smsgateway_username' => 'nullable|string|max:255',
            'smsgateway_password' => 'nullable|string|max:255',
            'smsgateway_sim_number' => 'nullable|integer|min:1|max:2',
        ]);

        $tenantIds = Tenant::where('status', 'active')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            try {
                $tenant = Tenant::find($tenantId);
                if (!$tenant) continue;

                $tenant->run(function () use ($data) {
                    foreach ($data as $key => $value) {
                        Setting::updateOrCreate(
                            ['key' => $key, 'group' => 'sms'],
                            ['value' => $value]
                        );
                    }
                });
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to save SMS settings for tenant {$tenantId}: {$e->getMessage()}");
            }
        }

        $settings = Setting::where('group', 'sms')
            ->get()
            ->pluck('value', 'key');

        return response()->json($settings);
    }
}
