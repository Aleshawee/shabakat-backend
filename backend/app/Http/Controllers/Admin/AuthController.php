<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

        $network = null;
        if ($admin->tenant_id) {
            $tenant = Tenant::on('mysql')->find($admin->tenant_id);
            if ($tenant) {
                $network = [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug ?? $tenant->id,
                ];
            }
        }

        return response()->json([
            'admin' => $admin,
            'token' => $token,
            'network' => $network,
        ]);
    }

    public function profile(Request $request)
    {
        $admin = $request->user();

        $profile = $admin->toArray();
        $profile['network'] = $admin->tenant();

        return response()->json($profile);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'تم تسجيل الخروج']);
    }
}
