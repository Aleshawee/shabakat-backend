<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LuckyBoxController;
use App\Http\Controllers\Api\LuckyWheelController;
use App\Http\Controllers\Api\AbsherController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\SportPredictionController;

/*
  API المستخدم — لتطبيق PWA / Flutter
  كل المسارات تحت prefix: /api
*/

// مصادقة المستخدم
Route::prefix('v1')->middleware('tenancy.by_domain_skip')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::get('auth/networks', [AuthController::class, 'networks']);
    Route::post('auth/send-otp', [AuthController::class, 'sendOtpReset']);
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
        Route::get('user/profile', [AuthController::class, 'profile']);
        Route::post('user/update', [AuthController::class, 'update']);
        Route::post('user/change-password', [AuthController::class, 'changePassword']);
        Route::get('user/transactions', [AuthController::class, 'transactions']);
        Route::get('user/history', [AuthController::class, 'history']);

        // المكافآت المتاحة (المستخدم)
        Route::get('rewards', [RewardController::class, 'index']);
        Route::post('rewards/{reward}/redeem', [RewardController::class, 'redeem']);
        Route::get('user/reward-cards', [RewardController::class, 'myCards']);

        // الفئات (معلومات الكروت)
        Route::get('categories', [CategoryController::class, 'index']);

        // استبدال كرت
        Route::post('cards/redeem', [CardController::class, 'redeem']);

        // الترفيه — صندوق الحظ (المستخدم)
        Route::get('lucky-boxes', [LuckyBoxController::class, 'index']);
        Route::post('lucky-boxes/{box}/play', [LuckyBoxController::class, 'play']);

        // الترفيه — عجلة الحظ (المستخدم)
        Route::get('lucky-wheels', [LuckyWheelController::class, 'index']);
        Route::post('lucky-wheels/{wheel}/spin', [LuckyWheelController::class, 'spin']);

        // أبشر — سلفة (المستخدم)
        Route::get('absher/settings', [AbsherController::class, 'settings']);
        Route::post('absher/request', [AbsherController::class, 'request']);

        // الترفيه — توقعات رياضية (المستخدم)
        Route::get('sport-events', [SportPredictionController::class, 'index']);
        Route::get('sport-events/{id}', [SportPredictionController::class, 'show']);
        Route::post('sport-events/{id}/predict', [SportPredictionController::class, 'predict']);
        Route::get('user/predictions', [SportPredictionController::class, 'myPredictions']);

        // إشعار تصفير النقاط
        Route::get('user/reset-notification', [\App\Http\Controllers\Api\ResetNotificationController::class, 'index']);

        // تحويل النقاط (المستخدم)
        Route::get('transfer/settings', [TransferController::class, 'settings']);
        Route::get('transfer/lookup-user', [TransferController::class, 'lookupUser']);
        Route::post('transfer/send', [TransferController::class, 'send']);
    });
});
