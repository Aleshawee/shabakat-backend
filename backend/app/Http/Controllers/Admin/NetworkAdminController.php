<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class NetworkAdminController extends Controller
{
    private function central(): \Illuminate\Database\Connection
    {
        return \Illuminate\Support\Facades\DB::connection('mysql');
    }

    public function index(Request $request)
    {
        $admin = $request->user();
        $query = Admin::on('mysql')->where('role', 'admin');

        if ($admin->isOwner()) {
            // Owner sees all network admins across all networks
            $admins = $query->get();
        } else {
            // Network admin sees only sub-admins in their network
            $admins = $query->where('tenant_id', $admin->tenant_id)->get();
        }

        return response()->json($admins);
    }

    public function store(Request $request)
    {
        $admin = $request->user();
        $tenantId = $admin->isOwner() ? $request->input('tenant_id') : $admin->tenant_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email',
            'password' => 'required|string|min:6',
        ]);

        $subAdmin = Admin::on('mysql')->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'tenant_id' => $tenantId,
            'status' => 'active',
        ]);

        return response()->json($subAdmin, 201);
    }

    public function update(Request $request, $id)
    {
        $admin = $request->user();
        $subAdmin = Admin::on('mysql')->findOrFail($id);

        // Network admin can only update sub-admins within their network
        if (!$admin->isOwner() && $subAdmin->tenant_id !== $admin->tenant_id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:admins,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $subAdmin->update($validated);

        return response()->json($subAdmin);
    }

    public function destroy(Request $request, $id)
    {
        $admin = $request->user();

        $subAdmin = Admin::on('mysql')->findOrFail($id);

        // Network admin can only delete sub-admins within their network
        if (!$admin->isOwner() && $subAdmin->tenant_id !== $admin->tenant_id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        if ($subAdmin->id === $admin->id) {
            return response()->json(['message' => 'لا يمكن حذف حسابك'], 422);
        }

        $subAdmin->delete();

        return response()->json(['message' => 'تم حذف المدير']);
    }
}
