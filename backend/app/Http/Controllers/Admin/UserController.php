<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PointTransaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->points_min !== null) {
            $query->where('points_balance', '>=', (int) $request->points_min);
        }
        if ($request->points_max !== null) {
            $query->where('points_balance', '<=', (int) $request->points_max);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $sortField = in_array($request->sort, ['points_balance', 'created_at', 'last_active_at']) ? $request->sort : 'created_at';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        $perPage = min((int) $request->per_page, 100) ?: 50;
        return response()->json($query->orderBy($sortField, $sortDir)->paginate($perPage));
    }

    public function stats(Request $request)
    {
        $query = User::query();

        return response()->json([
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'suspended' => (clone $query)->where('status', 'suspended')->count(),
            'banned' => (clone $query)->where('status', 'banned')->count(),
            'total_points' => (clone $query)->sum('points_balance'),
            'avg_points' => round((clone $query)->avg('points_balance') ?? 0, 1),
        ]);
    }

    public function show($id)
    {
        $user = User::with(['network', 'pointTransactions' => function ($q) {
            $q->latest()->limit(20);
        }, 'deviceFingerprints'])->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,suspended,banned',
            'points_balance' => 'nullable|integer|min:0',
        ]);

        $user->update($data);

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'update_user',
            'target_type' => 'user',
            'target_id' => $user->id,
            'details' => "تعديل المستخدم: {$user->name} ({$user->phone})",
        ]);

        return response()->json($user);
    }

    public function pointTransactions(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $query = $user->pointTransactions();
        if ($request->type) $query->where('type', $request->type);
        $perPage = min((int) $request->per_page, 100) ?: 50;
        return response()->json($query->latest()->paginate($perPage));
    }

    public function networkCards($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->networkCards()->with('category')->latest()->get());
    }

    public function rewardCards($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->rewardCards()->with('reward')->latest()->get());
    }

    public function addPoints(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($id);

        $finalBalance = $user->addPointsWithRepayment($validated['amount'], 'admin_add', null, $validated['note'] ?? 'إضافة نقاط من الإدارة');

        PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'admin_add',
            'amount' => $validated['amount'],
            'balance_after' => $finalBalance,
            'note' => $validated['note'] ?? 'إضافة نقاط من الإدارة',
        ]);

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'add_points',
            'target_type' => 'user',
            'target_id' => $user->id,
            'details' => "إضافة {$validated['amount']} نقطة للمستخدم: {$user->name} ({$user->phone})",
        ]);

        return response()->json([
            'message' => "تم إضافة {$validated['amount']} نقطة بنجاح",
            'user' => $user->fresh(),
        ]);
    }

    public function addPointsBulk(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        $query = User::query();

        $count = 0;
        $query->chunk(100, function ($users) use ($validated, &$count) {
            foreach ($users as $user) {
                $finalBalance = $user->addPointsWithRepayment($validated['amount'], 'admin_add', null, $validated['note'] ?? 'مكافأة من الإدارة');
                PointTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'admin_add',
                    'amount' => $validated['amount'],
                    'balance_after' => $finalBalance,
                    'note' => $validated['note'] ?? 'مكافأة من الإدارة',
                ]);
                $count++;
            }
        });

        ActivityLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'add_points_bulk',
            'target_type' => 'user',
            'target_id' => null,
            'details' => "إضافة {$validated['amount']} نقطة لـ {$count} مستخدم",
        ]);

        return response()->json([
            'message' => "تم إضافة {$validated['amount']} نقطة لـ {$count} مستخدم",
            'affected_users' => $count,
        ]);
    }
}
