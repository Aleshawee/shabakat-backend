<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PredictionSetting;
use Illuminate\Http\Request;

class PredictionSettingController extends Controller
{
    public function show(Request $request)
    {
        $admin = $request->user();
        $setting = PredictionSetting::firstOrCreate(
            [],
            ['is_enabled' => true, 'max_active_events' => 5, 'min_time_before_deadline' => 60, 'allow_prediction_edit' => false, 'edit_fee' => 0, 'auto_distribute_rewards' => true]
        );
        return response()->json($setting);
    }

    public function update(Request $request)
    {
        $admin = $request->user();
        $data = $request->validate([
            'is_enabled' => 'boolean',
            'max_active_events' => 'integer|min:1|max:20',
            'min_time_before_deadline' => 'integer|min:30',
            'allow_prediction_edit' => 'boolean',
            'edit_fee' => 'integer|min:0',
            'auto_distribute_rewards' => 'boolean',
        ]);

        $setting = PredictionSetting::firstOrCreate([]);
        $setting->update($data);
        return response()->json($setting);
    }
}
