<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    private function central(): \Illuminate\Database\Connection
    {
        return DB::connection('mysql');
    }

    public function stats()
    {
        $totalUsers = 0;
        $networks = Tenant::where('status', 'active')->get()->map(function ($t) use (&$totalUsers) {
            try {
                tenancy()->initialize($t);
                $count = \App\Models\User::count();
            } catch (\Exception $e) {
                $count = 0;
            } finally {
                if (tenancy()->initialized) {
                    tenancy()->end();
                }
            }
            $totalUsers += $count;
            return [
                'id' => $t->id,
                'name' => $t->name,
                'owner_name' => $t->owner_name,
                'slug' => $t->slug,
                'users_count' => $count,
                'status' => $t->status,
                'created_at' => $t->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'total_networks' => Tenant::count(),
            'total_admins' => Admin::on('mysql')->where('role', '!=', 'owner')->count(),
            'total_users' => $totalUsers,
            'networks' => $networks,
        ]);
    }

    public function networks()
    {
        $tenants = Tenant::with('domains')->get()->map(function ($t) {
            try {
                tenancy()->initialize($t);
                $usersCount = \App\Models\User::count();
            } catch (\Exception $e) {
                $usersCount = 0;
            } finally {
                if (tenancy()->initialized) {
                    tenancy()->end();
                }
            }
            $admins = Admin::on('mysql')->where('tenant_id', $t->id)->get(['id', 'name', 'email']);
            return [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'owner_name' => $t->owner_name,
                'phone' => $t->phone,
                'email' => $t->email,
                'commission_rate' => $t->commission_rate,
                'status' => $t->status,
                'domains' => $t->domains,
                'users_count' => $usersCount,
                'admins' => $admins,
                'created_at' => $t->created_at?->toISOString(),
            ];
        });

        return response()->json($tenants);
    }

    public function storeNetwork(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:active,inactive',
        ]);

        $slug = $request->input('slug');
        $subdomain = $request->input('subdomain');

        // Manual unique checks on central DB (tenancy is initialized by middleware)
        if ($this->central()->table('tenants')->where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Slug موجود مسبقاً', 'errors' => ['slug' => ['Slug موجود مسبقاً']]], 422);
        }
        $parentDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $fullDomain = $subdomain . '.' . $parentDomain;
        if ($this->central()->table('domains')->where('domain', $fullDomain)->exists()) {
            return response()->json(['message' => 'النطاق موجود مسبقاً', 'errors' => ['subdomain' => ['النطاق موجود مسبقاً']]], 422);
        }

        $tenant = Tenant::create([
            'id' => $slug,
            'name' => $validated['name'],
            'slug' => $slug,
            'owner_name' => $validated['owner_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'commission_rate' => $validated['commission_rate'] ?? 0,
            'status' => $validated['status'] ?? 'active',
        ]);

        $tenant->domains()->create(['domain' => $fullDomain]);

        // Copy default SMS settings from an existing active tenant
        try {
            $sourceTenant = Tenant::where('status', 'active')->where('id', '!=', $slug)->first();
            if ($sourceTenant) {
                $sourceTenant->run(function () use ($tenant) {
                    $smsSettings = Setting::where('group', 'sms')->get();
                    if ($smsSettings->isNotEmpty()) {
                        $tenant->run(function () use ($smsSettings) {
                            foreach ($smsSettings as $setting) {
                                Setting::updateOrCreate(
                                    ['key' => $setting->key, 'group' => 'sms'],
                                    ['value' => $setting->value]
                                );
                            }
                        });
                    }
                });
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to copy SMS settings to new tenant {$slug}: {$e->getMessage()}");
        }

        // Create default admin for the new network
        $defaultEmail = $request->input('admin_email', "admin@{$slug}.com");
        $defaultPassword = $request->input('admin_password', 'password');
        try {
            $admin = Admin::on('mysql')->create([
                'name' => $validated['owner_name'] . ' (مدير)',
                'email' => $defaultEmail,
                'password' => Hash::make($defaultPassword),
                'role' => 'admin',
                'tenant_id' => $slug,
                'status' => 'active',
            ]);
            $tenant->default_admin = [
                'email' => $admin->email,
                'password' => $defaultPassword,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to create admin for tenant {$slug}: {$e->getMessage()}");
        }

        return response()->json($tenant->load('domains'), 201);
    }

    public function updateNetwork(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'owner_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($request->has('slug') && $request->slug !== $tenant->slug) {
            if ($this->central()->table('tenants')->where('slug', $request->slug)->where('id', '!=', $id)->exists()) {
                return response()->json(['message' => 'Slug موجود مسبقاً', 'errors' => ['slug' => ['Slug موجود مسبقاً']]], 422);
            }
            $validated['slug'] = $request->slug;
        }

        $tenant->update($validated);

        return response()->json($tenant->load('domains'));
    }

    public function deleteNetwork($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return response()->json(['message' => 'تم حذف الشبكة']);
    }

    public function listAdmins($networkId)
    {
        Tenant::findOrFail($networkId);
        $admins = Admin::on('mysql')->where('tenant_id', $networkId)->get(['id', 'name', 'email']);

        return response()->json($admins);
    }

    public function storeAdmin(Request $request, $networkId)
    {
        Tenant::findOrFail($networkId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if (Admin::on('mysql')->where('email', $validated['email'])->exists()) {
            return response()->json(['message' => 'البريد الإلكتروني موجود مسبقاً', 'errors' => ['email' => ['البريد الإلكتروني موجود مسبقاً']]], 422);
        }

        $admin = Admin::on('mysql')->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'tenant_id' => $networkId,
            'status' => 'active',
        ]);

        return response()->json($admin, 201);
    }

    public function updateAdmin(Request $request, $networkId, $id)
    {
        Tenant::findOrFail($networkId);

        $admin = Admin::on('mysql')->where('tenant_id', $networkId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'password' => 'sometimes|string|min:6',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if (isset($validated['email'])) {
            $exists = Admin::on('mysql')->where('email', $validated['email'])->where('id', '!=', $id)->exists();
            if ($exists) {
                return response()->json(['message' => 'البريد الإلكتروني موجود مسبقاً', 'errors' => ['email' => ['البريد الإلكتروني موجود مسبقاً']]], 422);
            }
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $admin->update($validated);

        return response()->json($admin);
    }

    public function deleteAdmin($networkId, $id)
    {
        Tenant::findOrFail($networkId);

        $admin = Admin::on('mysql')->where('tenant_id', $networkId)->findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'تم حذف المدير']);
    }
}
