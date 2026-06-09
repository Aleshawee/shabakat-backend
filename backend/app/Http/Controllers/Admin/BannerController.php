<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::with('network');

        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->is_active !== null) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = min((int) $request->per_page, 100) ?: 50;
        return response()->json($query->orderBy('sort_order')->latest()->paginate($perPage));
    }

    public function stats(Request $request)
    {
        $query = Banner::query();

        return response()->json([
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
            'inactive' => (clone $query)->where('is_active', false)->count(),
            'expired' => (clone $query)->where('expires_at', '<', now())->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'expires_at' => 'nullable|date',
        ]);

        $data['is_active'] = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $banner = Banner::create($data);

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'create',
            'target_type' => 'banner',
            'target_id' => $banner->id,
            'details' => "إضافة بانر: {$banner->title}",
        ]);

        return response()->json($banner->load('network'), 201);
    }

    public function show($id)
    {
        $banner = Banner::with('network')->findOrFail($id);
        return response()->json($banner);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $data = $request->validate([
            'title' => 'string|max:255',
            'image' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'expires_at' => 'nullable|date',
        ]);

        $banner->update($data);

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'update',
            'target_type' => 'banner',
            'target_id' => $banner->id,
            'details' => "تعديل البانر: {$banner->title}",
        ]);

        return response()->json($banner->load('network'));
    }

    public function destroy(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'delete',
            'target_type' => 'banner',
            'target_id' => $banner->id,
            'details' => "حذف البانر: {$banner->title}",
        ]);

        return response()->json(['message' => 'تم الحذف']);
    }
}
