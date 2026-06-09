<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        $query = Reward::with('category');
        if ($request->search) $query->where('name', 'like', "%{$request->search}%");
        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'card_value' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $reward = Reward::create($data);
        ActivityLog::create(['admin_id' => $request->user()->id, 'action' => 'create', 'target_type' => 'reward', 'target_id' => $reward->id, 'details' => "إضافة مكافأة: {$reward->name}"]);
        return response()->json($reward, 201);
    }

    public function show($reward)
    {
        $reward = Reward::with('category')->findOrFail($reward);
        return response()->json($reward);
    }

    public function update(Request $request, $reward)
    {
        $reward = Reward::findOrFail($reward);
        $data = $request->validate([
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'integer|min:1',
            'card_value' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $reward->update($data);
        $reward->refresh();
        return response()->json($reward);
    }

    public function destroy($reward)
    {
        $reward = Reward::findOrFail($reward);
        $reward->delete();
        return response()->json(['message' => 'تم الحذف']);
    }
}
