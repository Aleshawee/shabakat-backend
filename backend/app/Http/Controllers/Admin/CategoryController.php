<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function stats(Request $request)
    {
        $admin = $request->user();
        $query = Category::withCount(['networkCards' => function ($q) {
            $q->where('status', 'active');
        }]);
        return response()->json($query->orderBy('name')->get());
    }

    public function index(Request $request)
    {
        $admin = $request->user();
        $query = Category::withCount(['networkCards' => function ($q) {
            $q->where('status', 'active');
        }]);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $admin = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'points' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $data['is_active'] ?? true;

        $category = Category::create($data);

        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => 'create',
            'target_type' => 'category',
            'target_id' => $category->id,
            'details' => "إضافة فئة جديدة: {$category->name}",
        ]);

        return response()->json($category, 201);
    }

    public function show($category)
    {
        $category = Category::findOrFail($category);
        return response()->json($category);
    }

    public function update(Request $request, $category)
    {
        $category = Category::findOrFail($category);
        $data = $request->validate([
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'points' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $category->update($data);
        $category->refresh();

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'update',
            'target_type' => 'category',
            'target_id' => $category->id,
            'details' => "تعديل الفئة: {$category->name}",
        ]);

        return response()->json($category);
    }

    public function destroy(Request $request, $category)
    {
        $category = Category::findOrFail($category);
        $category->delete();

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'delete',
            'target_type' => 'category',
            'target_id' => $category->id,
            'details' => "حذف الفئة: {$category->name}",
        ]);

        return response()->json(['message' => 'تم الحذف']);
    }


}
