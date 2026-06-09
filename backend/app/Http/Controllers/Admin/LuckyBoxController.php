<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LuckyBox;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LuckyBoxController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        $query = LuckyBox::with('prizes');

        return response()->json([
            'boxes' => $query->latest()->get(),
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $admin = $request->user();

        $data = $request->validate([
            'boxes' => 'required|array|max:4',
            'boxes.*.id' => 'nullable|integer|exists:lucky_boxes,id',
            'boxes.*.name' => 'required|string|max:255',
            'boxes.*.cost' => 'required|integer|min:0',
            'boxes.*.daily_limit' => 'nullable|integer|min:0',
            'boxes.*.color' => 'nullable|string|max:7',
            'boxes.*.is_active' => 'boolean',
            'boxes.*.prizes' => 'array',
            'boxes.*.prizes.*.id' => 'nullable|integer|exists:lucky_box_prizes,id',
            'boxes.*.prizes.*.name' => 'required|string|max:255',
            'boxes.*.prizes.*.type' => 'required|in:point,card,nothng',
            'boxes.*.prizes.*.value' => 'nullable|string|max:255',
            'boxes.*.prizes.*.weight' => 'required|integer|min:1',
        ]);

        $savedBoxIds = [];

        foreach ($data['boxes'] as $boxData) {
            $box = LuckyBox::updateOrCreate(
                ['id' => $boxData['id'] ?? null],
                [
                    'name' => $boxData['name'],
                    'cost' => $boxData['cost'],
                    'daily_limit' => $boxData['daily_limit'] ?? 0,
                    'color' => $boxData['color'] ?? '#4a4a8a',
                    'is_active' => $boxData['is_active'] ?? true,
                ]
            );
            $savedBoxIds[] = $box->id;

            $savedPrizeIds = [];
            foreach ($boxData['prizes'] ?? [] as $prizeData) {
                $prize = $box->prizes()->updateOrCreate(
                    ['id' => $prizeData['id'] ?? null],
                    [
                        'name' => $prizeData['name'],
                        'type' => $prizeData['type'],
                        'value' => $prizeData['value'] ?? '0',
                        'weight' => $prizeData['weight'],
                    ]
                );
                $savedPrizeIds[] = $prize->id;
            }

            $box->prizes()->whereNotIn('id', $savedPrizeIds)->delete();
        }

        LuckyBox::whereNotIn('id', $savedBoxIds)->delete();

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'update',
            'target_type' => 'lucky_box',
            'details' => 'تحديث إعدادات صناديق الحظ',
        ]);

        return $this->index($request);
    }
}
