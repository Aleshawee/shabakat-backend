<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\OtpCode;
use App\Models\PointTransaction;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function findUserByPhone(string $phone): ?User
    {
        $normalized = preg_replace('/^\+?967/', '', $phone);
        $user = User::where('phone', $normalized)->first();
        if (!$user) {
            $user = User::where('phone', '+967' . $normalized)->first();
        }
        return $user;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $phone = preg_replace('/^\+?967/', '', $validated['phone']);
        $ipKey = 'otp-register-ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            return response()->json(['message' => 'طلبات تسجيل كثيرة جداً من هذا العنوان'], 429);
        }
        RateLimiter::hit($ipKey, 3600);

        if (User::where('phone', $phone)->orWhere('phone', '+967' . $phone)->exists()) {
            return response()->json(['message' => 'رقم الهاتف مسجل مسبقاً'], 422);
        }

        Cache::put('pending_reg:' . $phone, [
            'name' => $validated['name'],
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
        ], now()->addMinutes(10));

        $this->sendRegistrationOtp($phone);

        return response()->json([
            'message' => 'تم إرسال رمز التفعيل إلى رقم هاتفك',
            'success' => true,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = $this->findUserByPhone($validated['phone']);

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'رقم الهاتف أو كلمة المرور غير صحيحة'], 401);
        }

        if (is_null($user->phone_verified_at)) {
            return response()->json(['message' => 'يرجى تفعيل رقم الهاتف أولاً'], 403);
        }

        $user->update(['last_active_at' => now()]);

        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'network' => tenancy()->initialized ? [
                'id' => tenancy()->tenant->id,
                'name' => tenancy()->tenant->name,
                'slug' => tenancy()->tenant->slug ?? tenancy()->tenant->id,
            ] : null,
        ]);
    }

    public function sendOtpReset(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $phoneKey = 'otp-phone:' . $phone;
        $ipKey = 'otp-ip:' . $request->ip();
        $cooldownKey = 'otp-cooldown:' . $phone;

        if (RateLimiter::tooManyAttempts($phoneKey, 3)) {
            $seconds = RateLimiter::availableIn($phoneKey);
            return response()->json(['message' => "انتظر {$seconds} ثانية قبل طلب رمز جديد"], 429);
        }
        if (RateLimiter::tooManyAttempts($ipKey, 20)) {
            return response()->json(['message' => 'طلبات كثيرة جداً من هذا العنوان'], 429);
        }
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);
            return response()->json(['message' => "انتظر {$seconds} ثانية بين كل طلب"], 429);
        }

        RateLimiter::hit($phoneKey, 3600);
        RateLimiter::hit($ipKey, 3600);
        RateLimiter::hit($cooldownKey, 60);

        $user = $this->findUserByPhone($validated['phone']);

        if (!$user) {
            return response()->json(['message' => 'رقم الهاتف غير مسجل'], 404);
        }

        $sent = $this->sendOtp($user);

        return response()->json([
            'message' => 'تم إرسال رمز التفعيل إلى رقم هاتفك',
            'success' => $sent,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|min:4|max:6',
        ]);

        $phone = preg_replace('/^\+?967/', '', $validated['phone']);

        $pendingData = Cache::get('pending_reg:' . $phone);

        if ($pendingData) {
            $cachedOtp = Cache::get('otp_reg:' . $phone);

            if (!$cachedOtp || $cachedOtp !== $validated['otp']) {
                return response()->json(['message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية'], 422);
            }

            Cache::forget('otp_reg:' . $phone);
            Cache::forget('pending_reg:' . $phone);

            $user = User::create($pendingData);
            $user->phone_verified_at = now();
            $user->save();

            $token = $user->createToken('user-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'verified' => true,
                'network' => tenancy()->initialized ? [
                    'id' => tenancy()->tenant->id,
                    'name' => tenancy()->tenant->name,
                    'slug' => tenancy()->tenant->slug ?? tenancy()->tenant->id,
                ] : null,
            ]);
        }

        $user = $this->findUserByPhone($validated['phone']);
        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $otpCode = OtpCode::where('user_id', $user->id)
            ->valid()
            ->latest()
            ->first();

        if (!$otpCode || $otpCode->code !== $validated['otp']) {
            return response()->json(['message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية'], 422);
        }

        $otpCode->update(['used_at' => now()]);
        $user->phone_verified_at = now();
        $user->save();

        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'verified' => true,
            'network' => tenancy()->initialized ? [
                'id' => tenancy()->tenant->id,
                'name' => tenancy()->tenant->name,
                'slug' => tenancy()->tenant->slug ?? tenancy()->tenant->id,
            ] : null,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'غير مصرح به. قم بتأكيد رمز التفعيل أولاً'], 401);
        }

        $user->password = Hash::make($validated['password']);
        $user->phone_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'تم تغيير كلمة المرور بنجاح',
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 401);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->toArray();
        $user['network'] = tenancy()->initialized ? [
            'id' => tenancy()->tenant->id,
            'name' => tenancy()->tenant->name,
            'slug' => tenancy()->tenant->slug ?? tenancy()->tenant->id,
        ] : null;
        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $user->update($request->only(['name', 'device_id']));
        return response()->json($user);
    }

    public function transactions(Request $request)
    {
        $transactions = PointTransaction::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(50);

        return response()->json($transactions);
    }

    public function networks()
    {
        $networks = Tenant::select('id', 'name', 'slug')->where('status', 'active')->get();
        return response()->json(['networks' => $networks]);
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $transactions = PointTransaction::where('user_id', $user->id)
            ->latest()
            ->take(100)
            ->get();

        return response()->json([
            'transactions' => $transactions,
            'balance' => $user->points_balance,
        ]);
    }

    private function sendOtp(User $user): bool
    {
        OtpCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        $sms = new SmsService();
        return $sms->sendOtp($user->phone, $code);
    }

    private function sendRegistrationOtp(string $phone): bool
    {
        Cache::forget('otp_reg:' . $phone);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put('otp_reg:' . $phone, $code, now()->addMinutes(5));

        $sms = new SmsService();
        return $sms->sendOtp($phone, $code);
    }
}
