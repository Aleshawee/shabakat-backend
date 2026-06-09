<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::with('network');

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('body', 'like', "%{$s}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->audience) {
            $query->where('audience', $request->audience);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = min((int) $request->per_page, 100) ?: 50;
        return response()->json($query->latest()->paginate($perPage));
    }

    public function stats(Request $request)
    {
        $query = Notification::query();

        return response()->json([
            'total' => (clone $query)->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'sent' => (clone $query)->where('status', 'sent')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image' => 'nullable|string|max:255',
            'audience' => 'required|in:all,specific',
            'target_user_ids' => 'nullable|array',
            'status' => 'required|in:draft,sent,cancelled',
        ]);

        $data['sent_at'] = $data['status'] === 'sent' ? now() : null;

        $notification = Notification::create($data);

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'create',
            'target_type' => 'notification',
            'target_id' => $notification->id,
            'details' => "إضافة إشعار: {$notification->title}",
        ]);

        return response()->json($notification->load('network'), 201);
    }

    public function show($id)
    {
        $notification = Notification::with('network')->findOrFail($id);
        return response()->json($notification);
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        $data = $request->validate([
            'title' => 'string|max:255',
            'body' => 'string',
            'image' => 'nullable|string|max:255',
            'audience' => 'in:all,specific',
            'target_user_ids' => 'nullable|array',
            'status' => 'in:draft,sent,cancelled',
        ]);

        $notification->update($data);

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'update',
            'target_type' => 'notification',
            'target_id' => $notification->id,
            'details' => "تعديل الإشعار: {$notification->title}",
        ]);

        return response()->json($notification->load('network'));
    }

    public function destroy(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'delete',
            'target_type' => 'notification',
            'target_id' => $notification->id,
            'details' => "حذف الإشعار: {$notification->title}",
        ]);

        return response()->json(['message' => 'تم الحذف']);
    }
}
